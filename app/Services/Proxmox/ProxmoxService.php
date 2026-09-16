<?php

namespace App\Services\Proxmox;

use App\Models\ProxmoxNode;
use App\Models\ProxmoxServer;
use App\Models\VpsService;

class ProxmoxService
{
    public function __construct(private ProxmoxApiClient $client) {}

    public function testConnection(ProxmoxServer $server): bool
    {
        try {
            $result = $this->client->get($server, 'nodes');
            $success = ! empty($result);
        } catch (\Exception) {
            $success = false;
        }

        $server->update(['is_active' => $success, 'last_checked_at' => now()]);

        return $success;
    }

    public function listNodes(ProxmoxServer $server): array
    {
        return $this->client->get($server, 'nodes');
    }

    public function syncNodes(ProxmoxServer $server): array
    {
        $nodes = $this->listNodes($server);
        $synced = [];

        foreach ($nodes['data'] ?? [] as $nodeData) {
            $nodeName = $nodeData['node'] ?? null;
            if (! $nodeName) {
                continue;
            }

            $status = $this->client->get($server, "nodes/{$nodeName}/status");
            $statusData = $status['data'] ?? [];

            $cpuTotal = $statusData['cpuinfo']['cpus'] ?? ($nodeData['maxcpu'] ?? 0);
            $cpuUsed = isset($statusData['cpu']) ? (float) $statusData['cpu'] * 100 : 0;
            $ramTotal = (int) (($statusData['memory']['total'] ?? ($nodeData['maxmem'] ?? 0)) / (1024 * 1024));
            $ramUsed = (int) (($statusData['memory']['used'] ?? 0) / (1024 * 1024));
            $diskTotal = (int) (($statusData['rootfs']['total'] ?? 0) / 1024);
            $diskUsed = (int) (($statusData['rootfs']['used'] ?? 0) / 1024);

            $node = ProxmoxNode::updateOrCreate(
                ['proxmox_server_id' => $server->id, 'node_name' => $nodeName],
                [
                    'cpu_total' => $cpuTotal,
                    'cpu_used' => $cpuUsed,
                    'ram_total_mb' => $ramTotal,
                    'ram_used_mb' => $ramUsed,
                    'disk_total_mb' => $diskTotal,
                    'disk_used_mb' => $diskUsed,
                    'is_online' => ($nodeData['status'] ?? 'offline') === 'online',
                    'last_synced_at' => now(),
                ]
            );
            $synced[] = $node;
        }

        $server->update(['is_active' => true, 'last_checked_at' => now()]);

        return $synced;
    }

    public function createVm(VpsService $vps, ProxmoxNode $node): array
    {
        $vmid = $this->getNextVmId($node);

        $result = $this->client->post($node->server, "nodes/{$node->node_name}/qemu", [
            'vmid' => $vmid,
            'name' => $vps->hostname ?? 'vm-'.$vmid,
            'cores' => $vps->cpu_cores,
            'memory' => $vps->ram_mb,
            'net0' => 'virtio,bridge=vmbr0',
            'ide2' => 'local:iso/'.$vps->os_template.'-amd64.iso,media=cdrom',
            'scsihw' => 'virtio-scsi-pci',
            'ostype' => 'l26',
            'boot' => 'order=scsi0;net0',
            'scsi0' => "local-lvm:{$vps->disk_mb}M",
        ]);

        if (! empty($result['data'])) {
            $vps->update(['vm_id' => (string) $vmid, 'proxmox_node_id' => $node->id, 'vm_status' => 'provisioning', 'provisioned_at' => now()]);
        }

        return $result;
    }

    public function startVm(VpsService $vps): array
    {
        $node = $vps->node;
        $result = $this->client->post($node->server, "nodes/{$node->node_name}/qemu/{$vps->vm_id}/status/start");
        $vps->update(['vm_status' => 'running']);

        return $result;
    }

    public function stopVm(VpsService $vps): array
    {
        $node = $vps->node;
        $result = $this->client->post($node->server, "nodes/{$node->node_name}/qemu/{$vps->vm_id}/status/stop");
        $vps->update(['vm_status' => 'stopped']);

        return $result;
    }

    public function shutdownVm(VpsService $vps): array
    {
        $node = $vps->node;

        return $this->client->post($node->server, "nodes/{$node->node_name}/qemu/{$vps->vm_id}/status/shutdown");
    }

    public function rebootVm(VpsService $vps): array
    {
        $node = $vps->node;

        return $this->client->post($node->server, "nodes/{$node->node_name}/qemu/{$vps->vm_id}/status/reboot");
    }

    public function deleteVm(VpsService $vps): array
    {
        $node = $vps->node;
        $this->stopVm($vps);

        return $this->client->delete($node->server, "nodes/{$node->node_name}/qemu/{$vps->vm_id}");
    }

    public function getVmStatus(VpsService $vps): array
    {
        $node = $vps->node;

        return $this->client->get($node->server, "nodes/{$node->node_name}/qemu/{$vps->vm_id}/status/current");
    }

    public function getNextVmId(ProxmoxNode $node): int
    {
        $vms = $this->client->get($node->server, "nodes/{$node->node_name}/qemu");
        $existingIds = collect($vms['data'] ?? [])->pluck('vmid')->toArray();

        $nextId = 100;
        while (in_array($nextId, $existingIds)) {
            $nextId++;
        }

        return $nextId;
    }
}
