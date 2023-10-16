<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * This class is only used to give the IDE a hint about the properties of the model.
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Model where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|Model whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Model create(array $attributes = [])
 * @method static Builder|Model updateOrCreate(array $attributes, array $values = [])
 * */
class Model extends \Illuminate\Database\Eloquent\Model
{

}
