<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Exception;

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
    ): stdClass {
        $response = self::getHttpClient()
            ->get('?lonlats='.$latitudeA.','.$longitudeA.'|'.$latitudeB.','.$longitudeB.'&profile=rail&alternativeidx=0&format=geojson');

        return json_decode($response->body());
    }
}
?>
