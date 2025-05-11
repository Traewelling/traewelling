<?php

namespace App\Models;

use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 
 *
 * @property int $id
 * @property ReportStatus $status Enum ReportStatus
 * @property string $subject_type
 * @property int $subject_id
 * @property ReportReason|null $reason Enum ReportReason or null.
 * @property string|null $description
 * @property int|null $reporter_id
 * @property int|null $admin_notification_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $reporter
 * @method static Builder<static>|Report newModelQuery()
 * @method static Builder<static>|Report newQuery()
 * @method static Builder<static>|Report query()
 * @method static Builder<static>|Report whereAdminNotificationId($value)
 * @method static Builder<static>|Report whereCreatedAt($value)
 * @method static Builder<static>|Report whereDescription($value)
 * @method static Builder<static>|Report whereId($value)
 * @method static Builder<static>|Report whereReason($value)
 * @method static Builder<static>|Report whereReporterId($value)
 * @method static Builder<static>|Report whereStatus($value)
 * @method static Builder<static>|Report whereSubjectId($value)
 * @method static Builder<static>|Report whereSubjectType($value)
 * @method static Builder<static>|Report whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Report extends Model
{
    use LogsActivity;

    protected $fillable = [
        'status', 'subject_type', 'subject_id', 'reason',
        'description', 'reporter_id', 'admin_notification_id'
    ];
    protected $casts    = [
        'status'                => ReportStatus::class,
        'subject_type'          => 'string',
        'subject_id'            => 'integer',
        'reason'                => ReportReason::class,
        'description'           => 'string',
        'reporter_id'           => 'integer',
        'admin_notification_id' => 'integer' //telegram message id
    ];

    public function reporter(): BelongsTo {
        return $this->belongsTo(User::class, 'reporter_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->dontSubmitEmptyLogs()
                         ->logOnlyDirty();
    }
}
