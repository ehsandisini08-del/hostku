<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $domain_name
 * @property string $tld
 * @property string|null $registrar
 * @property string|null $registrar_id
 * @property string|null $status
 * @property string|null $registration_date
 * @property string $expiration_date
 * @property bool $transfer_lock
 * @property bool $auto_renew
 * @property array|null $nameservers
 * @property bool $whois_privacy
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['domain_name', 'tld', 'registrar', 'registrar_id', 'status', 'registration_date', 'expiration_date', 'transfer_lock', 'auto_renew', 'nameservers', 'whois_privacy'])]
class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'nameservers' => 'array',
            'transfer_lock' => 'boolean',
            'auto_renew' => 'boolean',
            'whois_privacy' => 'boolean',
            'registration_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DomainContact::class);
    }

    public function registrationLogs(): HasMany
    {
        return $this->hasMany(DomainRegistrationLog::class);
    }

    public function service(): MorphOne
    {
        return $this->morphOne(Service::class, 'serviceable');
    }
}
