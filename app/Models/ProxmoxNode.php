<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['proxmox_server_id', 'node_name', 'cpu_total', 'cpu_used', 'ram_total_mb', 'ram_used_mb', 'disk_total_mb', 'disk_used_mb', 'is_online', 'last_synced_at'])]
class ProxmoxNode extends Model
{
    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(ProxmoxServer::class, 'proxmox_server_id');
    }

    public function vpsServices(): HasMany
    {
        return $this->hasMany(VpsService::class, 'proxmox_node_id');
    }
}
