<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ContributionActionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionHistory extends Model
{
    use HasUuids;

    protected $table = 'contribution_history';

    protected $fillable = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'xp_change',
        'level_before',
        'level_after',
        'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'action_type' => ContributionActionType::class,
        'entity_id' => 'integer',
        'xp_change' => 'integer',
        'level_before' => 'integer',
        'level_after' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
