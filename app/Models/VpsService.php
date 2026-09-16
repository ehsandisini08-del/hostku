<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['vps_plan_id', 'proxmox_node_id', 'vm_id', 'hostname', 'ip_address', 'ipv6_address', 'username', 'os_template', 'cpu_cores', 'ram_mb', 'disk_mb', 'bandwidth_mb', 'vm_status', 'provisioned_at', 'last_synced_at'])]
class VpsService extends Model
{
    protected function casts(): array
    {
        return [
            'provisioned_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(VpsPlan::class, 'vps_plan_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(ProxmoxNode::class, 'proxmox_node_id');
    }

    public function service(): MorphOne
    {
        return $this->morphOne(Service::class, 'serviceable');
    }
}
