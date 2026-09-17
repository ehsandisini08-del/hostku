<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $hosting_service_id
 * @property string $email_address
 * @property string $mailbox_user
 * @property string $domain
 * @property int $quota_mb
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read HostingService $hostingService
 */
#[Fillable(['hosting_service_id', 'email_address', 'mailbox_user', 'domain', 'quota_mb'])]
class HostingEmail extends Model
{
    public function hostingService(): BelongsTo
    {
        return $this->belongsTo(HostingService::class);
    }
}
