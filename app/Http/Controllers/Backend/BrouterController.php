<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use stdClass;

abstract class BrouterController extends Controller
{
    public static function getHttpClient(): PendingRequest {
        return Http::baseUrl(config('trwl.brouter_url'))
                   ->timeout(config('trwl.brouter_timeout'));
    }

    public static function getGeoJSON(
        float $latitudeA,
        float $longitudeA,
        float $latitudeB,
        float $longitudeB
    ): ?stdClass {
        https://brouter.de/brouter?lonlats=49.214089,6.944311|48.876976,2.35912&profile=rail&alternativeidx=0&format=geojson
        $response = self::getHttpClient()
            ->get('brouter?lonlats='.$longitudeA.','.$latitudeA.'|'.$longitudeB.','.$latitudeB.'&profile=rail&alternativeidx=0&format=geojson');
        Log::debug('Brouter URL is '.$response->effectiveUri());
        if (!$response->ok()) return null;

        return json_decode($response->body());
    }
}
?>
