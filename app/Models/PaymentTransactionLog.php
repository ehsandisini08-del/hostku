<?php

namespace App\Models;

use Database\Factories\PaymentTransactionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $transaction_id
 * @property string $gateway
 * @property string $event_type
 * @property array $raw_payload
 * @property bool $processed
 * @property Carbon|null $processed_at
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PaymentTransactionLog extends Model
{
    /** @use HasFactory<PaymentTransactionLogFactory> */
    use HasFactory;

    protected $fillable = ['transaction_id', 'gateway', 'event_type', 'raw_payload', 'processed', 'processed_at', 'error_message'];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }
}
