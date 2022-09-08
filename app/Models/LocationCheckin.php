<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationCheckin extends Model
{
    use HasFactory;

    protected $fillable = ['status_id', 'user_id', 'location_id', 'arrival', 'departure'];
    protected $appends  = ['socialText'];
    protected $casts    = [
        'status_id'   => 'integer',
        'user_id'     => 'integer',
        'location_id' => 'integer',
        'arrival'     => 'datetime',
        'departure'   => 'datetime',
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function getSocialTextAttribute(): string {
        if (isset($this->status->body)) {
            if ($this->status->event !== null) {
                $eventIntercept = ' ' . __('controller.transport.social-post-for', [
                        'hashtag' => $this->status->event->hashtag
                    ]);
            }

            $appendix = strtr(' (@ :name:eventIntercept) #NowTräwelling', [
                ':name'           => $this->location->name,
                ':eventIntercept' => isset($eventIntercept) ? ' ' . $eventIntercept : ''
            ]);

            $appendixLength = strlen($appendix) + 30;
            $postText       = substr($this->status->body, 0, 280 - $appendixLength);
            if (strlen($postText) !== strlen($this->status->body)) {
                $postText .= '...';
            }
            return $postText . $appendix;
        }

        if (isset($this->status->event)) {
            return __('location.social-post-with-event', [
                'name'    => $this->location->name,
                'hashtag' => $this->status->event->hashtag,
            ]);
        }

        return __('location.social-post', [
            'name' => $this->location->name,
        ]);
    }
}
