<?php

namespace App\Dto\Transport;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     title="TrainStation",
 *     description="train station model",
 *     @OA\Xml(
 *        name="TrainStation"
 *     )
 * )
 */
class TrainStation
{
    /**
     * @OA\Property (
     *     title="id",
     *     description="id",
     *     example="4711"
     * )
     *
     * @var integer
     */
    public readonly int $id;

    /**
     * @OA\Property(
     *     title="name",
     *     description="name of the station",
     *     example="Karlsruhe Hbf"
     * )
     *
     * @var string
     */
    public readonly string $name;

    /**
     * @OA\Property(
     *     title="latitude",
     *     description="latitude of the station",
     *     example="48.991591"
     * )
     *
     * @var float
     */
    public readonly float $latitude;

    /**
     * @OA\Property(
     *     title="longitude",
     *     description="longitude of the station",
     *     example="8.400538"
     * )
     *
     * @var float
     */
    public readonly float $longitude;

    /**
     * @OA\Property(
     *     title="ibnr",
     *     description="IBNR of the station",
     *     example="8000191"
     * )
     *
     * @var int
     */
    public readonly int $ibnr;

    /**
     * @OA\Property (
     *     title="rilIdentifier",
     *     description="Identifier specified in 'Richtline 100' of the Deutsche Bahn",
     *     nullable="true",
     *     example="RK"
     * )
     *
     * @var ?string
     */
    public readonly ?string $rilIdentifier;

    public function setId(int $id): TrainStation {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): TrainStation {
        $this->name = $name;
        return $this;
    }

    public function setLatitude(float $latitude): TrainStation {
        $this->latitude = $latitude;
        return $this;
    }

    public function setLongitude(float $longitude): TrainStation {
        $this->longitude = $longitude;
        return $this;
    }

    public function setIbnr(int $ibnr): TrainStation {
        $this->ibnr = $ibnr;
        return $this;
    }

    public function setRilIdentifier(?string $rilIdentifier): TrainStation {
        $this->rilIdentifier = $rilIdentifier;
        return $this;
    }



    public static function fromModel(\App\Models\TrainStation $station) {
        $dto = new self();
        $dto->setId($station->id)
            ->setName($station->name)
            ->setIbnr($station->ibnr)
            ->setLatitude($station->latitude)
            ->setLongitude($station->longitude)
            ->setRilIdentifier($station->rilIdentifier);
        return $dto;
    }
}
