<?php declare(strict_types=1);

namespace App\Dto;

use App\Enum\PointReason;
use App\Models\Event;
use App\Models\Checkin;
use App\Virtual\Models\Trip;
use Illuminate\Database\Eloquent\Collection;

class CheckinSuccess
{

    public readonly int         $id;
    public readonly string      $reason;
    public readonly ?int        $distance;
    public readonly int         $duration;
    public readonly int         $points;
    public readonly PointReason $pointReason;
    public readonly string      $lineName;
    public readonly string      $socialText;
    public readonly Collection  $alsoOnThisConnection;
    public readonly ?Event      $event;
    public readonly bool        $forced;

    /**
     * @param int         $id
     * @param int         $distance
     * @param int         $duration
     * @param int         $points
     * @param PointReason $pointReason
     * @param string      $lineName
     * @param string      $socialText
     * @param Collection  $alsoOnThisConnection
     * @param Event|null  $event
     * @param bool        $forced
     * @param string      $reason
     */
    public function __construct(
        int         $id,
        int         $distance,
        int         $duration,
        int         $points,
        PointReason $pointReason,
        string      $lineName,
        string      $socialText,
        Collection  $alsoOnThisConnection,
        ?Event      $event,
        bool        $forced = false,
        string      $reason = ''
    ) {
        $this->id                   = $id;
        $this->distance             = $distance;
        $this->duration             = $duration;
        $this->points               = $points;
        $this->pointReason          = $pointReason;
        $this->lineName             = $lineName;
        $this->socialText           = $socialText;
        $this->alsoOnThisConnection = $alsoOnThisConnection;
        $this->event                = $event;
        $this->forced               = $forced;
        $this->reason               = $reason;
    }

}
