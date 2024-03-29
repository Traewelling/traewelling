<?php

namespace App\View\Components;

use App\Dto\CheckinSuccess as CheckinSuccessDto;
use App\Enum\PointReason;
use App\Models\Event;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class CheckinSuccess extends Component
{
    public int         $id;
    public int         $distance;
    public int         $duration;
    public int         $points;
    public PointReason $pointReason;
    public string      $lineName;
    public string      $socialText;
    public Collection  $alsoOnThisConnection;
    public ?Event      $event;
    public bool        $forced;
    public string      $reason;

    /**
     * @param int        $id
     * @param int        $distance
     * @param int        $duration
     * @param int        $points
     * @param int        $pointReason
     * @param string     $lineName
     * @param string     $socialText
     * @param Collection $alsoOnThisConnection
     * @param Event|null $event
     * @param bool       $forced
     * @param string     $reason
     */
    public function __construct(
        int         $id,
        int         $distance,
        int         $duration,
        int         $points,
        int         $pointReason,
        string      $lineName,
        string      $socialText,
        Collection  $alsoOnThisConnection,
        ?Event      $event,
        bool        $forced,
        string      $reason
    ) {
        $this->id                   = $id;
        $this->distance             = $distance;
        $this->duration             = $duration;
        $this->points               = $points;
        $this->pointReason          = PointReason::from(value: $pointReason);
        $this->lineName             = $lineName;
        $this->socialText           = $socialText;
        $this->alsoOnThisConnection = $alsoOnThisConnection;
        $this->event                = $event;
        $this->forced               = $forced;
        $this->reason               = $reason;
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render(): View|string|Closure {
        return view('components.checkin-success');
    }
}
