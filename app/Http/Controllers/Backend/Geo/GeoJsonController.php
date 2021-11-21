<?php

namespace App\Http\Controllers\Backend\Geo;

use App\Http\Controllers\Controller;
use App\Models\PolyLine;
use App\Models\TrainStation;
use Exception;
use InvalidArgumentException;

abstract class GeoJsonController extends Controller
{
    public static function isValid(string $jsonString): bool {
        $data = json_decode($jsonString);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON');
        }
        if (!isset($data->type)) {
            throw new InvalidArgumentException('Missing type');
        }
        if (!isset($data->features)) {
            throw new InvalidArgumentException('Missing features');
        }
        if (!is_array($data->features)) {
            throw new InvalidArgumentException('Invalid features');
        }
        foreach ($data->features as $feature) {
            if (!isset($feature->type)) {
                throw new InvalidArgumentException('Missing type');
            }
            if (!isset($feature->properties)) {
                throw new InvalidArgumentException('Missing properties');
            }
            if (!isset($feature->geometry)) {
                throw new InvalidArgumentException('Missing geometry');
            }
        }
        return true;
    }

    /**
     * UNTESTED
     *
     * @param PolyLine     $polyline
     * @param TrainStation $origin
     * @param TrainStation $destination
     *
     * @return string
     * @throws \JsonException
     * @todo Parse with departure/arrival times
     */
    public static function trimPolyline(PolyLine $polyline, TrainStation $origin, TrainStation $destination): string {

        if (!self::isValid($polyline->polyline)) {
            throw new InvalidArgumentException('Invalid polyline');
        }

        $jsonObject = json_decode($polyline->polyline);

        $passedOrigin      = false;
        $passedDestination = false;
        foreach ($jsonObject->features as $index => $feature) {
            if (isset($feature->properties->id) && $feature->properties->id == $origin->ibnr) {
                $passedOrigin = true;
            }
            if (
                (!$passedOrigin && !$passedDestination)
                ||
                ($passedOrigin && $passedDestination)
            ) {
                unset($jsonObject->features[$index]);
            }
            if (isset($feature->properties->id) && $feature->properties->id == $destination->ibnr) {
                $passedDestination = true;
            }
        }

        $geoJson = json_encode($jsonObject);

        if (!self::isValid($geoJson)) {
            throw new Exception('Error while trimming polyline');
        }
        return json_encode($jsonObject, JSON_THROW_ON_ERROR);
    }

}
