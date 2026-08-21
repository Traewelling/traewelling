<?php

declare(strict_types=1);

namespace App\Dto\Transport;

use App\Http\Resources\StationIdentifierResource;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Station', description: 'train station model', required: ['id', 'uuid', 'name', 'latitude', 'longitude', 'ibnr', 'rilIdentifier', 'identifiers'], xml: new OA\Xml(name: 'Station'))]
class Station
{
    #[OA\Property(title: 'id', description: 'id', example: '4711')]
    public readonly int $id;

    #[OA\Property(
        title: 'uuid',
        description: 'Stable identifier of this station. Will become the primary key later.',
        format: 'uuid',
        example: '00000000-0000-0000-0000-000000000000',
        nullable: true,
    )]
    public readonly ?string $uuid;

    #[OA\Property(title: 'name', description: 'name of the station', example: 'Karlsruhe Hbf')]
    public readonly string $name;

    #[OA\Property(title: 'latitude', description: 'latitude of the station', example: '48.991591')]
    public readonly float $latitude;

    #[OA\Property(title: 'longitude', description: 'longitude of the station', example: '8.400538')]
    public readonly float $longitude;

    #[OA\Property(title: 'ibnr', description: 'IBNR of the station', example: '8000191', nullable: true)]
    public readonly ?int $ibnr;

    #[OA\Property(
        title: 'rilIdentifier',
        description: 'Identifier specified in \'Richtline 100\' of the Deutsche Bahn',
        example: 'RK',
        nullable: true,
    )]
    public readonly ?string $rilIdentifier;

    #[OA\Property(
        title: 'identifiers',
        description: 'List of external station identifiers (IBNR, RIL100, IFOPT, Wikidata, MOTIS). Null when not loaded.',
        type: 'array',
        items: new OA\Items(ref: StationIdentifierResource::class),
        nullable: true,
    )]
    public readonly ?array $identifiers;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function setIbnr(?int $ibnr): self
    {
        $this->ibnr = $ibnr;

        return $this;
    }

    public function setRilIdentifier(?string $rilIdentifier): self
    {
        $this->rilIdentifier = $rilIdentifier;

        return $this;
    }

    public function setIdentifiers(?array $identifiers): self
    {
        $this->identifiers = $identifiers;

        return $this;
    }

    public static function fromModel(\App\Models\Station $station): self
    {
        $identifiers = $station->relationLoaded('stationIdentifiers')
            ? StationIdentifierResource::collection($station->stationIdentifiers)->resolve()
            : null;

        $dto = new self();
        $dto->setId($station->id)
            ->setUuid($station->uuid)
            ->setName($station->name)
            ->setIbnr(null) // TODO: Kann das hier mittelfristig raus? Wo wird das noch über dieses DTO genutzt?
            ->setLatitude($station->latitude)
            ->setLongitude($station->longitude)
            ->setRilIdentifier(null) // TODO: Kann das hier mittelfristig raus? Wo wird das noch über dieses DTO genutzt?
            ->setIdentifiers($identifiers);

        return $dto;
    }
}
