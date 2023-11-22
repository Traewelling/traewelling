<?php

namespace App\Dto\GeoJson;

use Illuminate\Support\Collection;
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
class FeatureCollection implements \JsonSerializable
{
    /**
     * @OA\Property (
     *     title="type",
     *     example="FeatureCollection"
     * )
     * @var string
     */
    private string $type;


    /**
     * @OA\Property(
     *     property="features",
     *     type="array",
     *     @OA\Items(
     *         ref="#/components/schemas/Coordinate"
     *     )
     * )
     */
    public Collection $features;

    public function __construct(Collection $features, string $type = 'FeatureCollection') {
        $this->features = $features;
        $this->type = $type;
    }

    public function toArray(): array {
        return [
            'type'     => $this->type,
            'features' => $this->features
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
