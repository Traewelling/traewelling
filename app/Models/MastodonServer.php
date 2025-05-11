<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property string $domain
 * @property string $client_id
 * @property string $client_secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SocialLoginProfile> $socialProfiles
 * @property-read int|null $social_profiles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MastodonServer extends Model
{
    protected $fillable = ['domain', 'client_id', 'client_secret'];
    protected $casts    = [
        'id' => 'integer',
    ];
    protected $hidden   = ['client_id', 'client_secret'];

    public function socialProfiles(): HasMany {
        return $this->hasMany(SocialLoginProfile::class, 'mastodon_server', 'id');
    }
}
