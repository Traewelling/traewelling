<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Polyline",
 *     description="Polyline of a single status as GeoJSON Feature",
 *     @OA\Xml(
 *         name="Polyline"
 *     )
 * )
 */
class Polyline
{
    /**
     * @OA\Property (
     *     title="type",
     *     example="Feature"
     * )
     * @var string
     */
    private $type;


    /**
     * @OA\Property(
     *     property="geometry",
     *     @OA\Property (
     *          property="type",
     *          example="LineString",
     *     ),
     *     @OA\Property (
     *          property="coordinates",
     *          type="array",
     *          @OA\Items(
     *              example="[[8.39767,49.01625],[8.45947,49.06576],[8.52401,49.01625],[8.39218,48.88729],[8.25759,49.00544],[8.30703,49.07476],[8.39080,49.01535]]"
     *
     *          )
     *     )
     * )
     * @var object
     */
    private $geometry;

    /**
     * @OA\Property (
     *     property="properties",
     *     @OA\Property(
     *          property="statusId",
     *          example=1337,
     *          type="integer"
     *     )
     * )
     * @var object
     */
    private $properties;
}
