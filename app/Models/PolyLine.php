<?php

namespace App\Models;

use App\Services\PolylineStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PolyLine extends Model
{
    private PolylineStorageService $polylineStorageService;
    protected                      $fillable = ['hash', 'polyline', 'source', 'parent_id'];
    protected                      $casts    = [
        'id'     => 'integer',
        'source' => 'string', //enum['hafas', 'brouter'] in database
    ];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->polylineStorageService = new PolylineStorageService();
    }

    public function trips(): HasMany {
        return $this->hasMany(Trip::class, 'polyline_id', 'id');
    }

    public function parent(): HasOne {
        return $this->hasOne(PolyLine::class, 'parent_id', 'id');
    }

    public function __get($key) {
        // check if the polyline is empty
        if ($key === 'polyline') {
            return $this->polylineStorageService->getOrCreate($this);
        }

        return parent::__get($key);
    }

    public function delete(): ?bool {
        $this->polylineStorageService->delete($this->hash);
        return parent::delete();
    }
}
