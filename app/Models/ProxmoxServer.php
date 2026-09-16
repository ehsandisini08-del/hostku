<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'host', 'port', 'auth_type', 'token_id', 'token_secret', 'is_active', 'last_checked_at'])]
class ProxmoxServer extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_checked_at' => 'datetime'];
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(ProxmoxNode::class);
    }
}
