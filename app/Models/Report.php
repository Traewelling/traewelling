<?php

namespace App\Models;

use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Report extends Model
{
    use LogsActivity;

    protected $fillable = ['status', 'subject_type', 'subject_id', 'reason', 'description', 'reporter_id'];
    protected $casts    = [
        'status'       => ReportStatus::class,
        'subject_type' => 'string',
        'subject_id'   => 'integer',
        'reason'       => ReportReason::class,
        'description'  => 'string',
        'reporter_id'  => 'integer',
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
