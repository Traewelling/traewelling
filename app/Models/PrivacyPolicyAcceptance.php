<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $user_id
 * @property string $privacy_policy_id
 * @property Carbon $accepted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PrivacyPolicy $privacyPolicy
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance wherePrivacyPolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicyAcceptance whereUuid($value)
 *
 * @mixin \Eloquent
 */
class PrivacyPolicyAcceptance extends Model
{
    use HasUuids;

    protected $fillable = [
        'privacy_policy_id',
        'user_id',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function privacyPolicy(): BelongsTo
    {
        return $this->belongsTo(PrivacyPolicy::class);
    }
}
