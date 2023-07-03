<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EventSuggestion;
use Illuminate\Support\Facades\DB;

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
