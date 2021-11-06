<?php

namespace App\Models;

use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * @property int    user_id
 * @property string body
 * @property int    business
 * @property int    visibility
 */
class Status extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'body', 'business', 'event_id', 'visibility'];
    protected $hidden   = ['user_id', 'business'];
    protected $appends  = ['favorited', 'socialText', 'statusInvisibleToMe'];
    protected $casts    = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'business'   => 'integer', //TODO: Change to Enum Cast with Laravel 9
        'visibility' => 'integer', //TODO: Change to Enum Cast with Laravel 9
        'event_id'   => 'integer',
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
            'controller.transport.social-post',
            preg_match('/\s/', $this->trainCheckin->HafasTrip->linename),
            [
                'lineName'    => $this->trainCheckin->HafasTrip->linename,
                'destination' => $this->trainCheckin->Destination->name
            ]
        );
        if ($this->event !== null) {
            $postText = trans_choice(
                'controller.transport.social-post-with-event',
                preg_match('/\s/', $this->trainCheckin->HafasTrip->linename),
                [
                    'lineName'    => $this->trainCheckin->HafasTrip->linename,
                    'destination' => $this->trainCheckin->Destination->name,
                    'hashtag'     => $this->event->hashtag
                ]
            );
        }


        if (isset($this->body)) {
            if ($this->event !== null) {
                $eventIntercept = __('controller.transport.social-post-for', [
                    ':hashtag' => $this->event->hashtag
                ]);
            }

            $appendix = strtr(' (@ :linename ➜ :destination:eventIntercept) #NowTräwelling ', [
                ':linename'       => $this->trainCheckin->HafasTrip->linename,
                ':destination'    => $this->trainCheckin->Destination->name,
                ':eventIntercept' => isset($eventIntercept) ? ' ' . $eventIntercept : ''
            ]);

            $appendixLength = strlen($appendix) + 30;
            $postText       = substr($this->body, 0, 280 - $appendixLength);
            if (strlen($postText) != strlen($this->body)) {
                $postText .= '...';
            }
            $postText .= $appendix;
        }

        return $postText;
    }

    /**
     * When is a status invisible?
     * 0=public, 1=unlisted, 2=Followers, 3=Private
     * @return bool
     */
    public function getStatusInvisibleToMeAttribute(): bool {
        if ($this->user->userInvisibleToMe) {
            return true;
        }
        if ((Auth::check() && Auth::id() == $this->user_id) || $this->visibility == StatusVisibility::PUBLIC) {
            return false;
        }
        $visible = false;
        if ($this->visibility == StatusVisibility::FOLLOWERS) {
            $visible = (Auth::check() && Auth::user()->follows->contains('id', $this->user_id));
        }
        return !$visible;
    }
}
