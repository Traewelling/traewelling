<?php

namespace App\Models;

use App\Enum\ProfileLinkName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'url',
    ];

    protected $casts = [
        'name' => ProfileLinkName::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
