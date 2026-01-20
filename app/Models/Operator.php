<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    use HasFactory;

    protected $table = 'hafas_operators'; // todo: rename table & foreign keys in database

    protected $fillable = ['wikidata_id', 'name'];

    protected $casts = [
        'id' => 'integer',
        'wikidata_id' => 'string',
        'name' => 'string',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'operator_id', 'id');
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(OperatorIdentifier::class, 'operator_id', 'id');
    }
}
