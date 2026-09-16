<?php

namespace App\Models;

use Database\Factories\HostingPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $disk_space_mb
 * @property int|null $bandwidth_mb
 * @property int $max_websites
 * @property int $max_databases
 * @property int $max_emails
 * @property int $max_ftp
 * @property int $max_subdomains
 * @property string|null $server_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['product_id', 'disk_space_mb', 'bandwidth_mb', 'max_websites', 'max_databases', 'max_emails', 'max_ftp', 'max_subdomains', 'server_type'])]
class HostingPlan extends Model
{
    /** @use HasFactory<HostingPlanFactory> */
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
