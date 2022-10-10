<?php

namespace App\Models;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * @property int              user_id
 * @property string           body
 * @property Business         business
 * @property int              event_id
 * @property StatusVisibility visibility
 */
class Status extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'body', 'business', 'visibility', 'event_id', 'tweet_id'];
    protected $hidden   = ['user_id', 'business'];
    protected $appends  = ['favorited', 'socialText', 'statusInvisibleToMe'];
    protected $casts    = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'business'   => Business::class,
        'visibility' => StatusVisibility::class,
        'event_id'   => 'integer',
        'tweet_id'   => 'string',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class);
    }

    public function trainCheckin(): HasOne {
        return $this->hasOne(TrainCheckin::class);
    }

    public function event(): HasOne {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function getFavoritedAttribute(): ?bool {
        if (!Auth::check()) {
            return null;
        }
        return $this->likes->contains('user_id', Auth::id());
    }

    public function getSocialTextAttribute(): string {
        $postText = trans_choice(
            key:     'controller.transport.social-post',
            number:  preg_match('/\s/', $this->trainCheckin->HafasTrip->linename),
            replace: [
                         'lineName'    => $this->trainCheckin->HafasTrip->linename,
                         'destination' => $this->trainCheckin->Destination->name
                     ]
        );
        if ($this->event !== null) {
            $postText = trans_choice(
                key:     'controller.transport.social-post-with-event',
                number:  preg_match('/\s/', $this->trainCheckin->HafasTrip->linename),
                replace: [
                             'lineName'    => $this->trainCheckin->HafasTrip->linename,
                             'destination' => $this->trainCheckin->Destination->name,
                             'hashtag'     => $this->event->hashtag
                         ]
            );
        }


        if (isset($this->body)) {
            if ($this->event !== null) {
                $eventIntercept = __('controller.transport.social-post-for', [
                    'hashtag' => $this->event->hashtag
                ]);
            }

            $appendix = strtr(' (@ :linename ➜ :destination:eventIntercept) #NowTräwelling', [
                ':linename'       => $this->trainCheckin->HafasTrip->linename,
                ':destination'    => $this->trainCheckin->Destination->name,
                ':eventIntercept' => isset($eventIntercept) ? ' ' . $eventIntercept : ''
            ]);

            $appendixLength = strlen($appendix) + 30;
            $postText       = substr($this->body, 0, 280 - $appendixLength);
            if (strlen($postText) !== strlen($this->body)) {
                $postText .= '...';
            }
            $postText .= $appendix;
        }

        return $postText;
    }

    /**
     * @deprecated ->   replaced by $user->can(...) / $user->cannot(...) /
     *                  request()->user()->can(...) / request()->user()->cannot(...)
     */
    public function getStatusInvisibleToMeAttribute(): bool {
        return !request()?->user()?->can('view', $this);
    }
}
