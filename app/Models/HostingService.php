<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $hosting_plan_id
 * @property int $hosting_server_id
 * @property string|null $domain
 * @property string|null $username
 * @property string|null $server_ip
 * @property string|null $db_name
 * @property string|null $db_user
 * @property string|null $db_pass
 * @property string|null $php_version
 * @property bool $ssl_active
 * @property string|null $panel_url
 * @property Carbon|null $provisioned_at
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['hosting_plan_id', 'hosting_server_id', 'domain', 'username', 'server_ip', 'db_name', 'db_user', 'db_pass', 'php_version', 'ssl_active', 'panel_url', 'provisioned_at', 'last_synced_at'])]
class HostingService extends Model
{
    protected function casts(): array
    {
        return [
            'provisioned_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'ssl_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HostingPlan::class, 'hosting_plan_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(HostingServer::class, 'hosting_server_id');
    }

    public function service(): MorphOne
    {
        return $this->morphOne(Service::class, 'serviceable');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(HostingEmail::class);
    }
}
