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
                                                     ->where('train_stations.ibnr', '>', 8000000)
                                                     ->whereNull('train_stations.wikidata_id')
                                                     ->select('train_stations.*')
                                                     ->limit(50)
                                                     ->distinct()
                                                     ->get();

        return view('open-data.wikidata.index', [
            'destinationStationsWithoutWikidata' => $destinationStationsWithoutWikidata
        ]);
    }
}
