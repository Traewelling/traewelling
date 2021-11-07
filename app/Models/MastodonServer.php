<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MastodonServer extends Model
{
    protected $fillable = ['domain', 'client_id', 'client_secret'];
    protected $casts    = [
        'id' => 'integer',
    ];

    public function socialProfiles(): HasMany {
        return $this->hasMany(SocialLoginProfile::class, 'mastodon_server', 'id');
    }
}
