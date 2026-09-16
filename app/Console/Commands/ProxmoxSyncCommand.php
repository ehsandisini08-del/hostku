<?php

namespace App\Console\Commands;

use App\Models\ProxmoxNode;
use App\Models\ProxmoxServer;
use App\Services\Proxmox\ProxmoxService;
use Illuminate\Console\Command;

class ProxmoxSyncCommand extends Command
{
    protected $signature = 'proxmox:sync';

    protected $description = 'Sync Proxmox node resource usage';

    public function handle(ProxmoxService $proxmox): void
    {
        $servers = ProxmoxServer::where('is_active', true)->get();

        foreach ($servers as $server) {
            try {
                $nodeList = $proxmox->listNodes($server);

                foreach ($nodeList['data'] ?? [] as $nodeData) {
                    $status = $nodeData['status'] ?? 'offline';

                    ProxmoxNode::updateOrCreate([
                        'proxmox_server_id' => $server->id,
                        'node_name' => $nodeData['node'] ?? 'unknown',
                    ], [
                        'cpu_total' => $nodeData['maxcpu'] ?? 0,
                        'cpu_used' => (float) ($nodeData['cpu'] ?? 0) * 100,
                        'ram_total_mb' => (int) (($nodeData['maxmem'] ?? 0) / (1024 * 1024)),
                        'ram_used_mb' => (int) (($nodeData['mem'] ?? 0) / (1024 * 1024)),
                        'disk_total_mb' => (int) (($nodeData['maxdisk'] ?? 0) / (1024 * 1024)),
                        'disk_used_mb' => (int) (($nodeData['disk'] ?? 0) / (1024 * 1024)),
                        'is_online' => $status === 'online',
                        'last_synced_at' => now(),
                    ]);
                }

                $server->update(['last_checked_at' => now()]);
            } catch (\Exception) {
                continue;
            }
        }

        $this->info('Proxmox sync complete.');
    }
}
