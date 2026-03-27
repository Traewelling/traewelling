<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ContributionActionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property ContributionActionType $action_type
 * @property string $entity_type
 * @property int $entity_id
 * @property int $xp_change
 * @property int $level_before
 * @property int $level_after
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereLevelAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereLevelBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContributionHistory whereXpChange($value)
 *
 * @mixin \Eloquent
 */
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
