<?php

namespace App\Models;

use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property ReportStatus $status
 * @property string $subject_type
 * @property int $subject_id
 * @property ReportReason|null $reason
 * @property string|null $description
 * @property int|null $reporter_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $admin_notification_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $reporter
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereAdminNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Report extends Model
{
    use LogsActivity;

    protected $fillable = [
        'status', 'subject_type', 'subject_id', 'reason',
        'description', 'reporter_id', 'admin_notification_id',
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'subject_type' => 'string',
        'subject_id' => 'integer',
        'reason' => ReportReason::class,
        'description' => 'string',
        'reporter_id' => 'integer',
        'admin_notification_id' => 'integer', // telegram message id
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
