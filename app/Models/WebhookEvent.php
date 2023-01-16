<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Builder
 */
class WebhookEvent extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['webhook_id', 'event'];
    protected $casts    = [
        'webhook_id' => 'integer',
        'event' => 'string'
    ];

    public function webhook(): BelongsTo {
        return $this->belongsTo(Webhook::class, null, 'webhook_id');
    }
}
