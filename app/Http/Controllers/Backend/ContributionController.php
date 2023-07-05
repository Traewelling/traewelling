<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Admin\EventController as AdminEventBackend;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Notifications\EventSuggestionProcessed;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ContributionController extends Controller
{
    public static function getEventSuggestion() {
        //ToDo: Add "locking"
        return EventSuggestion::where('processed', false)
                              ->where('end', '>', DB::raw('CURRENT_TIMESTAMP'))
                              ->orderBy('begin')
                              ->firstOrFail();
    }

}
