<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @method static Builder<static>|WikidataEntity newModelQuery()
 * @method static Builder<static>|WikidataEntity newQuery()
 * @method static Builder<static>|WikidataEntity query()
 * @mixin \Eloquent
 */
class WikidataEntity extends Model
{
    protected $keyType  = 'string';
    protected $fillable = ['id', 'data', 'last_updated_at'];
    protected $casts    = [
        'id'              => 'string',
        'data'            => 'array',
        'last_updated_at' => 'datetime'
    ];
}
