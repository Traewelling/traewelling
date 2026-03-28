<?php

namespace App\Models;

use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property ReportStatus $status
 * @property string $subject_type
 * @property int $subject_id
 * @property ReportReason|null $reason
 * @property string|null $description
 * @property int|null $reporter_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $telegram_notification_id
 * @property string|null $matrix_notification_id
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read User|null $reporter
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereMatrixNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereTelegramNotificationId($value)
 *
 * @mixin \Eloquent
 */
class Report extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'status', 'subject_type', 'subject_id', 'reason',
        'description', 'reporter_id', 'telegram_notification_id', 'matrix_notification_id',
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'subject_type' => 'string',
        'subject_id' => 'integer',
        'reason' => ReportReason::class,
        'description' => 'string',
        'reporter_id' => 'integer',
        'telegram_notification_id' => 'integer',
        'matrix_notification_id' => 'string',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->dontLogEmptyChanges()
            ->logOnlyDirty();
    }
}
