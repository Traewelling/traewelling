<?php

namespace App\Models;

use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int          $id         @todo change to uuid (currently not used so easy without break something)
 * @property ReportStatus $status
 * @property string       $subject_type
 * @property int          $subject_id @todo make it a string to support uuid
 * @property ReportReason $reason
 * @property string       $description
 * @property int          $reporter_id
 * @property int          $admin_notification_id
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
