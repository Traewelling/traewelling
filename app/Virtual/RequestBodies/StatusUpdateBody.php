<?php

namespace App\Virtual\RequestBodies;

/**
 * @OA\Schema(
 *      title="StatusUpdateBody",
 *      description="Status Update Body",
 *      @OA\Xml(
 *         name="StatusUpdateBody"
 *      ),
 *      @OA\Property (
 *          property="body",
 *          maxLength=280,
 *          description="Status-Text to be displayed alongside the checkin",
 *          example="Wow. This train is extremely crowded!",
 *          nullable=true
 *      ),
 *      @OA\Property (
 *          property="business",
 *          ref="#/components/schemas/Business"
 *      ),
 *      @OA\Property (
 *          property="visibility",
 *          ref="#/components/schemas/StatusVisibility",
 *      ),
 *      @OA\Property (
 *          property="eventId",
 *          description="The ID of the event this status is related to - or null",
 *          example="1",
 *          nullable=true
 *      ),
 *      @OA\Property (
 *          property="manualDeparture",
 *          description="Manual departure time set by the user",
 *          format="date",
 *          example="2020-01-01 12:00:00",
 *          nullable=true
 *      ),
 *      @OA\Property (
 *          property="manualArrival",
 *          description="Manual arrival time set by the user",
 *          format="date",
 *          example="2020-01-01 13:00:00",
 *          nullable=true
 *      ),
 *      @OA\Property (
 *          property="destinationId",
 *          description="Destination station id",
 *          example="1",
 *          nullable=true
 *      ),
 *      @OA\Property (
 *          property="destinationArrivalPlanned",
 *          description="Destination arrival time",
 *          format="date",
 *          example="2020-01-01 13:00:00",
 *          nullable=true
 *      )
 * )
 */
class StatusUpdateBody
{
}
