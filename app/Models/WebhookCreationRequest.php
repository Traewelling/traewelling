<?php

namespace App\Models;

use App\Enum\WebhookEventEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\AuthCode;

/**
 * @mixin Builder
 */
class WebhookCreationRequest extends Model
{
    public $timestamps = false;
    protected $fillable = ['id', 'events', 'url'];
    protected $casts = [
        'id' => 'string',
        'events' => 'string',
        'url' => 'string',
    ];

    public function authCode(): BelongsTo
    {
        return $this->belongsTo(AuthCode::class, null, 'id');
    }
}
