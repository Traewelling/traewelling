<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="FeatureCollection",
 *     description="featurecollection of multiple GeoJson points",
 *     @OA\Xml(
 *         name="FeatureCollection"
 *     )
 * )
 */
class FeatureCollection
{
    /**
     * @OA\Property (
     *     title="type",
     *     example="FeatureCollection"
     * )
     * @var string
     */
    private $type;


    /**
     * @OA\Property(
     *     property="features",
     *     type="array",
     *     @OA\Items(
     *         ref="#/components/schemas/Coordinate"
     *     )
     * )
     * @var object
     */
    private $features;

}
