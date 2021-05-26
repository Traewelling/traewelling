<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class EventController
 * @package App\Http\Controllers
 * @deprecated Please move all functions to Frontend and Backend folder
 */
class EventController extends Controller
{

    public static function activeEvents(): ?Collection {
        $now = Carbon::now();

        return Event::where([
                                ['begin', '<=', $now],
                                ['end', '>=', $now]
                            ])->get();
    }
}
