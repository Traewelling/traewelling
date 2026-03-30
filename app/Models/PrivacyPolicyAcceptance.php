<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
