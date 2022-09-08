<?php

namespace App\Http\Controllers\Backend\Location;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\MastodonController;
use App\Http\Controllers\Backend\Social\TwitterController;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Location;
use App\Models\LocationCheckin;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Exception;

abstract class LocationCheckinController extends Controller
{
    /**
     * @throws NotConnectedException
     */
    public static function checkin(
        User             $user,
        Location         $location,
        Carbon           $arrival,
        Carbon           $departure,
        Business         $business = Business::PRIVATE,
        StatusVisibility $visibility = StatusVisibility::PUBLIC,
        ?string          $body = null,
        ?Event           $event = null,
        bool             $postOnTwitter = false,
        bool             $postOnMastodon = false,
    ) {
        try {
            $status = Status::create([
                                         'user_id'      => $user->id,
                                         'body'         => $body,
                                         'business'     => $business,
                                         'visibility'   => $visibility,
                                         'type'         => 'location',
                                         'event_id'     => $event?->id,
                                         'effective_at' => $arrival->toDateTimeString(),
                                     ]);

            $locationCheckin = LocationCheckin::create([
                                                           'status_id'   => $status->id,
                                                           'user_id'     => $user->id,
                                                           'location_id' => $location->id,
                                                           'arrival'     => $arrival->toDateTimeString(),
                                                           'departure'   => $departure->toDateTimeString(),
                                                       ]);

            if ($postOnTwitter && $user->socialProfile?->twitter_id !== null) {
                TwitterController::postStatus($status);
            }
            if ($postOnMastodon && $user->socialProfile?->mastodon_id !== null) {
                MastodonController::postStatus($status);
            }

            return $locationCheckin;
        } catch (Exception $exception) {
            if (isset($status)) {
                // Delete status if it was created and rethrow exception, so it can be handled by the caller
                $status->delete();
            }
            throw $exception;
        }
    }
}
