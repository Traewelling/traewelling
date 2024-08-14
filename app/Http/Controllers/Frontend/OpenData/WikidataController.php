<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend\OpenData;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\View\View;

class WikidataController extends Controller
{
    public function indexHelpPage(): View {

        //get stations the user was travelling recently, without a wikidata id
        $destinationStationsWithoutWikidata = Station::join('train_stopovers', 'train_stations.id', '=', 'train_stopovers.train_station_id')
                                                     ->join('train_checkins', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                                                     ->where('train_checkins.user_id', auth()->id())
                                                     ->where('train_stations.ibnr', '>', 1000000)
                                                     ->whereNull('train_stations.wikidata_id')
                                                     ->select('train_stations.*')
                                                     ->limit(50)
                                                     ->orderByDesc('train_checkins.created_at')
                                                     ->distinct()
                                                     ->get();

        return view('open-data.wikidata.index', [
            'destinationStationsWithoutWikidata' => $destinationStationsWithoutWikidata
        ]);
    }
}
