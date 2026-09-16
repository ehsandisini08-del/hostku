<?php

namespace App\Models;

use Database\Factories\VpsPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $cpu_cores
 * @property int $ram_mb
 * @property int $disk_mb
 * @property int|null $bandwidth_mb
 * @property int $ipv4_count
 * @property int $ipv6_count
 * @property array|null $os_templates
 * @property string $network_bridge
 * @property string|null $storage_pool
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['product_id', 'cpu_cores', 'ram_mb', 'disk_mb', 'bandwidth_mb', 'ipv4_count', 'ipv6_count', 'os_templates', 'network_bridge', 'storage_pool'])]
class VpsPlan extends Model
{
    /** @use HasFactory<VpsPlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'os_templates' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
