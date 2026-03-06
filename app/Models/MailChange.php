<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property string $old_email
 * @property string $new_email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange whereNewEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange whereOldEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailChange whereUserId($value)
 *
 * @mixin \Eloquent
 */
class MailChange extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
