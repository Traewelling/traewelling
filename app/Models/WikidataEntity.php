<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
