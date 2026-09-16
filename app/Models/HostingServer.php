<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $hostname
 * @property string $ip_address
 * @property string $panel_type
 * @property string $api_url
 * @property string $api_token
 * @property string|null $api_username
 * @property int|null $max_accounts
 * @property bool $is_active
 * @property int|null $ssh_port
 * @property string|null $ssh_user
 * @property string|null $ssh_key_path
 * @property string|null $web_server
 * @property string|null $php_version
 * @property string|null $base_path
 * @property string|null $ssl_email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'hostname', 'ip_address', 'panel_type', 'api_url', 'api_token', 'api_username', 'max_accounts', 'is_active', 'ssh_port', 'ssh_user', 'ssh_key_path', 'web_server', 'php_version', 'base_path', 'ssl_email'])]
class HostingServer extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function services(): HasMany
    {
        return $this->hasMany(HostingService::class);
    }

    public function isCustomSsh(): bool
    {
        return $this->panel_type === 'custom_ssh';
    }
}