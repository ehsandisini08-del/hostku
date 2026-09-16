<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $domain_id
 * @property string $action
 * @property string $status
 * @property array|null $request_payload
 * @property array|null $response_payload
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DomainRegistrationLog extends Model
{
    protected $fillable = ['domain_id', 'action', 'status', 'request_payload', 'response_payload', 'error_message'];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
