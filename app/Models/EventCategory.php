<?php

namespace App\Models;

use App\Enum\EventCategory as EventCategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'category'];
    protected $casts    = [
        'event_id' => 'int',
        'category' => EventCategoryEnum::class,
    ];

    public function event(): BelongsTo {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
