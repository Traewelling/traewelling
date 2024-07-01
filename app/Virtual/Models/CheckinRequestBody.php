<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="CheckinRequestBody",
 *     description="Fields for creating a train checkin",
 *     @OA\Property(property="body", type="string", maxLength=280, nullable=true, example="Meine erste Fahrt nach Knuffingen!"),
 *     @OA\Property(property="business", ref="#/components/schemas/Business",),
 *     @OA\Property(property="visibility", ref="#/components/schemas/StatusVisibility",),
 *     @OA\Property(property="eventId", type="integer", nullable=true, example="1", description="Id of an event the status should be connected to"),
 *     @OA\Property(property="toot", type="boolean", nullable=true, example="false", description="Should this status be posted to mastodon?"),
 *     @OA\Property(property="chainPost", type="boolean", nullable=true, example="false", description="Should this status be posted to mastodon as a chained post?"),
 *     @OA\Property(property="ibnr", type="boolean", nullable=true, example="true", description="If true, the `start` and `destination` properties can be supplied as an ibnr. Otherwise they should be given as the Träwelling-ID. Default behavior is `false`."),
 *     @OA\Property(property="tripId", type="string", nullable=true, example="b37ff515-22e1-463c-94de-3ad7964b5cb8", description="The tripId for the to be checked in train"),
 *     @OA\Property(property="lineName", type="string", nullable=true, example="S 4", description="The line name for the to be checked in train"),
 *     @OA\Property(property="start", type="integer", example="8000191", description="The Station-ID of the starting point (see `ibnr`)"),
 *     @OA\Property(property="destination", type="integer", example="8000192", description="The Station-ID of the destination point (see `ibnr`)"),
 *     @OA\Property(property="departure", type="string", format="date-time", example="2022-12-19T20:41:00+01:00", description="Timestamp of the departure"),
 *     @OA\Property(property="arrival", type="string", format="date-time", example="2022-12-19T20:42:00+01:00", description="Timestamp of the arrival"),
 *     @OA\Property(property="force", type="boolean", nullable=true, example="false", description="If true, the checkin will be created, even if a colliding checkin exists. No points will be awarded."),
 *     @OA\Property(property="with", type="array", @OA\Items(type="integer", example="1"), example="[1, 2]", nullable=true, description="If set, the checkin will be created for all given users as well. The user creating the checkin must be allowed to checkin for the other users. Max. 10 users."),
 * )
 */
class CheckinRequestBody
{


}
