<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property int $year
 * @property array<array-key, mixed> $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearInReviewCache whereYear($value)
 *
 * @mixin \Eloquent
 */
class YearInReviewCache extends Model
{
    use HasUuids;

    protected $table = 'year_in_review_cache';

    protected $fillable = [
        'user_id',
        'year',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
