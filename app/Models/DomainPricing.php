<?php

namespace App\Models;

use Database\Factories\DomainPricingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $tld
 * @property float $registration_price
 * @property float $renewal_price
 * @property float $transfer_price
 * @property int $min_years
 * @property int $max_years
 * @property bool $is_premium
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['product_id', 'tld', 'registration_price', 'renewal_price', 'transfer_price', 'min_years', 'max_years', 'is_premium'])]
class DomainPricing extends Model
{
    /** @use HasFactory<DomainPricingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'registration_price' => 'decimal:2',
            'renewal_price' => 'decimal:2',
            'transfer_price' => 'decimal:2',
            'is_premium' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
