<?php

namespace App\Models;

use App\Enum\ProfileLinkName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string          $id
 * @property string          $user_id
 * @property ProfileLinkName $name
 * @property string          $url
 *
 * @property-read User       $user
 * @property-read string     $created_at
 * @property-read string     $updated_at
 */
class ProfileLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'url',
    ];

    protected $casts = [
        'id'         => 'uuid',
        'name'       => ProfileLinkName::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
