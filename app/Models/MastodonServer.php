<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int                                      $id
 * @property string                                   $domain
 * @property string                                   $client_id
 * @property string                                   $client_secret
 * @property Carbon|null                              $created_at
 * @property Carbon|null                              $updated_at
 * @property-read Collection<int, SocialLoginProfile> $socialProfiles
 * @property-read int|null                            $social_profiles_count
 * @method static Builder<static>|MastodonServer newModelQuery()
 * @method static Builder<static>|MastodonServer newQuery()
 * @method static Builder<static>|MastodonServer query()
 * @method static Builder<static>|MastodonServer whereClientId($value)
 * @method static Builder<static>|MastodonServer whereClientSecret($value)
 * @method static Builder<static>|MastodonServer whereCreatedAt($value)
 * @method static Builder<static>|MastodonServer whereDomain($value)
 * @method static Builder<static>|MastodonServer whereId($value)
 * @method static Builder<static>|MastodonServer whereUpdatedAt($value)
 * @mixin Eloquent
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
