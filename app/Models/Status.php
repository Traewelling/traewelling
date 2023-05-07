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

    protected $fillable = ['user_id', 'body', 'business', 'visibility', 'event_id', 'tweet_id', 'mastodon_post_id'];
    protected $hidden   = ['user_id', 'business'];
    protected $appends  = ['favorited', 'socialText', 'statusInvisibleToMe', 'description'];
    protected $casts    = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'business'         => Business::class,
        'visibility'       => StatusVisibility::class,
        'event_id'         => 'integer',
        'tweet_id'         => 'string',
        'mastodon_post_id' => 'string',
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

    public function tags(): HasMany {
        return $this->hasMany(StatusTag::class, 'status_id', 'id');
    }

    public function getFavoritedAttribute(): ?bool {
        if (!Auth::check()) {
            return null;
        }
        return $this->likes->contains('user_id', Auth::id());
    }

    public function showLikeUi(): bool {
        if (!$this->user->likes_enabled) {
            return false;
        }

        if (!Auth::check()) {
            return true;
        }

        return Auth::user()->likes_enabled;
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

    public function getDescriptionAttribute(): string {
        return __('description.status', [
            'username'    => $this->user->name,
            'origin'      => $this->trainCheckin->Origin->name .
                             ($this->trainCheckin->Origin->rilIdentifier ?
                                 ' (' . $this->trainCheckin->Origin->rilIdentifier . ')' : ''),
            'destination' => $this->trainCheckin->Destination->name .
                             ($this->trainCheckin->Destination->rilIdentifier ?
                                 ' (' . $this->trainCheckin->Destination->rilIdentifier . ')' : ''),
            'date'        => $this->trainCheckin->departure->isoFormat(__('datetime-format')),
            'lineName'    => $this->trainCheckin->HafasTrip->linename
        ]);
    }

    /**
     * @deprecated ->   replaced by $user->can(...) / $user->cannot(...) /
     *                  request()->user()->can(...) / request()->user()->cannot(...)
     */
    public function getStatusInvisibleToMeAttribute(): bool {
        return !request()?->user()?->can('view', $this);
    }
}
