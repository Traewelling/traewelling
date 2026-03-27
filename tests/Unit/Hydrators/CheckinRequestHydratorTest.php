<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\Enum\Business;
use App\Enum\StationIdentifierType;
use App\Enum\StatusVisibility;
use App\Exceptions\DataProviderException;
use App\Http\Requests\CheckinRequest;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Event;
use App\Models\Station;
use App\Repositories\EventRepository;
use App\Repositories\StationRepository;
use App\Repositories\TripRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Tests\Unit\UnitTestCase;

class CheckinRequestHydratorTest extends UnitTestCase
{
    private Authenticatable $user;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createMock(Authenticatable::class);
    }

    /**
     * @throws DataProviderException
     * @throws Exception
     */
    public function test_hydrate_from_api_with_full_array()
    {
        $origin = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $stationRepository = $this->mock(StationRepository::class);
        $tripRepository = $this->mock(TripRepository::class);
        $eventRepository = $this->mock(EventRepository::class);
        $stationRepository->shouldReceive('getById')->with(4321)->andReturn($destination);
        $stationRepository->shouldReceive('getById')->with(1234)->andReturn($origin);
        $tripRepository->shouldReceive('getByIdentifier')->once();
        $eventRepository->shouldReceive('getById')->never();

        $request = new CheckinRequest([
            'body' => 'Test',
            'business' => Business::PRIVATE->value,
            'visibility' => StatusVisibility::PUBLIC->value,
            'eventId' => null,
            'toot' => false,
            'chainPost' => true,
            'ibnr' => false,
            'tripId' => '1234',
            'lineName' => 'ICE 123',
            'start' => 1234,
            'destination' => 4321,
            'departure' => '2021-12-12 15:00:00',
            'arrival' => '2021-12-12 15:32:45',
            'force' => false,
        ]);

        $hydrator = new CheckinRequestHydrator(
            validated: $request,
            user: $this->user,
            dto: null,
            stationRepository: $stationRepository,
            eventRepository: $eventRepository,
            tripRepository: $tripRepository
        );
        $dto = $hydrator->hydrateFromApi();

        $this->assertFalse($dto->postOnMastodonFlag);
        $this->assertFalse($dto->forceFlag);
        $this->assertTrue($dto->chainFlag);
        $this->assertEquals('Test', $dto->body);
        $this->assertEquals(Carbon::parse('2021-12-12 15:00:00'), $dto->departure);
        $this->assertEquals(Carbon::parse('2021-12-12 15:32:45'), $dto->arrival);
        $this->assertEquals($origin, $dto->origin);
        $this->assertEquals($destination, $dto->destination);
        $this->assertNotEquals($dto->origin, $dto->destination);
        $this->assertEquals(Business::PRIVATE, $dto->travelReason);
        $this->assertEquals(StatusVisibility::PUBLIC, $dto->statusVisibility);
        $this->assertNull($dto->event);
    }

    /**
     * @throws DataProviderException
     * @throws Exception
     */
    public function test_hydrate_from_api_with_nullable_fields()
    {
        $origin = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $stationRepository = $this->mock(StationRepository::class);
        $tripRepository = $this->mock(TripRepository::class);
        $eventRepository = $this->mock(EventRepository::class);
        $stationRepository->shouldReceive('getById')->with(4321)->andReturn($destination);
        $stationRepository->shouldReceive('getById')->with(1234)->andReturn($origin);
        $tripRepository->shouldReceive('getByIdentifier')->once();
        $eventRepository->shouldReceive('getById')->never();

        $request = new CheckinRequest([
            'business' => Business::PRIVATE->value,
            'visibility' => StatusVisibility::PUBLIC->value,
            'toot' => false,
            'chainPost' => true,
            'tripId' => '1234',
            'lineName' => 'ICE 123',
            'start' => 1234,
            'destination' => 4321,
            'departure' => '2021-12-12 15:00:00',
            'arrival' => '2021-12-12 15:32:45',
        ]);

        $hydrator = new CheckinRequestHydrator(
            validated: $request,
            user: $this->user,
            dto: null,
            stationRepository: $stationRepository,
            eventRepository: $eventRepository,
            tripRepository: $tripRepository
        );
        $dto = $hydrator->hydrateFromApi();

        $this->assertFalse($dto->postOnMastodonFlag);
        $this->assertFalse($dto->forceFlag);
        $this->assertTrue($dto->chainFlag);
        $this->assertNull($dto->body);
        $this->assertEquals(Carbon::parse('2021-12-12 15:00:00'), $dto->departure);
        $this->assertEquals(Carbon::parse('2021-12-12 15:32:45'), $dto->arrival);
        $this->assertEquals($origin, $dto->origin);
        $this->assertEquals($destination, $dto->destination);
        $this->assertNotEquals($dto->origin, $dto->destination);
        $this->assertEquals(Business::PRIVATE, $dto->travelReason);
        $this->assertEquals(StatusVisibility::PUBLIC, $dto->statusVisibility);
        $this->assertNull($dto->event);
    }

    /**
     * @throws DataProviderException
     * @throws Exception
     */
    public function test_hydrate_from_api_with_event_and_legacy_ibnr()
    {
        $origin = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $event = $this->createMock(Event::class);
        $stationRepository = $this->mock(StationRepository::class);
        $tripRepository = $this->mock(TripRepository::class);
        $eventRepository = $this->mock(EventRepository::class);
        $stationRepository->shouldReceive('getByIdentifier')->with(4321, StationIdentifierType::DE_DB_IBNR)->andReturn($destination);
        $stationRepository->shouldReceive('getByIdentifier')->with(1234, StationIdentifierType::DE_DB_IBNR)->andReturn($origin);
        $tripRepository->shouldReceive('getByIdentifier')->once();
        $eventRepository->shouldReceive('getById')->once()->with(123)->andReturn($event);

        $request = new CheckinRequest([
            'business' => Business::PRIVATE->value,
            'visibility' => StatusVisibility::PUBLIC->value,
            'toot' => false,
            'chainPost' => true,
            'tripId' => '1234',
            'lineName' => 'ICE 123',
            'start' => 1234,
            'destination' => 4321,
            'departure' => '2021-12-12 15:00:00',
            'arrival' => '2021-12-12 15:32:45',
            'ibnr' => true,
            'eventId' => 123,
        ]);

        $hydrator = new CheckinRequestHydrator(
            validated: $request,
            user: $this->user,
            dto: null,
            stationRepository: $stationRepository,
            eventRepository: $eventRepository,
            tripRepository: $tripRepository
        );
        $dto = $hydrator->hydrateFromApi();

        $this->assertFalse($dto->postOnMastodonFlag);
        $this->assertFalse($dto->forceFlag);
        $this->assertTrue($dto->chainFlag);
        $this->assertNull($dto->body);
        $this->assertEquals(Carbon::parse('2021-12-12 15:00:00'), $dto->departure);
        $this->assertEquals(Carbon::parse('2021-12-12 15:32:45'), $dto->arrival);
        $this->assertEquals($origin, $dto->origin);
        $this->assertEquals($destination, $dto->destination);
        $this->assertNotEquals($dto->origin, $dto->destination);
        $this->assertEquals(Business::PRIVATE, $dto->travelReason);
        $this->assertEquals(StatusVisibility::PUBLIC, $dto->statusVisibility);
        $this->assertEquals($event, $dto->event);
    }

    public static function identifierDataProvider(): array
    {
        return [
            'IFOPT' => ['de-0815-1234:56:78', StationIdentifierType::IFOPT, 'de-0815-4321:56:78', StationIdentifierType::IFOPT],
            'RIL100' => ['RK', StationIdentifierType::DE_DB_RIL100, 'MIH', StationIdentifierType::DE_DB_RIL100],
            'Motis' => ['foobar-baz', StationIdentifierType::MOTIS, 'quux-quuz', StationIdentifierType::MOTIS],
            'wikidata' => ['Q1234', StationIdentifierType::WIKIDATA_ID, 'Q12345', StationIdentifierType::WIKIDATA_ID],
            'IBNR' => ['8000191', StationIdentifierType::DE_DB_IBNR, '8000192', StationIdentifierType::DE_DB_IBNR],
            'Mixed, Wikidata + Motis' => ['Q1234', StationIdentifierType::WIKIDATA_ID, 'foobar-baz', StationIdentifierType::MOTIS],
            'Mixed, IBNR + RIL100' => ['RK', StationIdentifierType::DE_DB_RIL100, '8000191', StationIdentifierType::DE_DB_IBNR],
        ];
    }

    /**
     * @throws DataProviderException
     * @throws Exception
     */
    #[DataProvider('identifierDataProvider')]
    public function test_hydrate_from_api_with_lookup_identifiers(string $originIdentifier, StationIdentifierType $originType, string $destinationIdentifier, StationIdentifierType $destinationType)
    {
        $origin = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $stationRepository = $this->mock(StationRepository::class);
        $tripRepository = $this->mock(TripRepository::class);
        $stationRepository->shouldReceive('getById')->never();
        $stationRepository->shouldReceive('getByIdentifier')->with($originIdentifier, $originType)->andReturn($origin);
        $stationRepository->shouldReceive('getByIdentifier')->with($destinationIdentifier, $destinationType)->andReturn($destination);
        $tripRepository->shouldReceive('getByIdentifier')->once();

        $request = new CheckinRequest([
            'business' => Business::PRIVATE->value,
            'visibility' => StatusVisibility::PUBLIC->value,
            'tripId' => '1234',
            'lineName' => 'ICE 123',
            'startIdentifier' => $originIdentifier,
            'startIdentifierType' => $originType->value,
            'destinationIdentifier' => $destinationIdentifier,
            'destinationIdentifierType' => $destinationType->value,
            'departure' => '2021-12-12 15:00:00',
            'arrival' => '2021-12-12 15:32:45',
        ]);

        $hydrator = new CheckinRequestHydrator(
            validated: $request,
            user: $this->user,
            dto: null,
            stationRepository: $stationRepository,
            eventRepository: null,
            tripRepository: $tripRepository
        );
        $dto = $hydrator->hydrateFromApi();

        $this->assertFalse($dto->postOnMastodonFlag);
        $this->assertFalse($dto->forceFlag);
        $this->assertFalse($dto->chainFlag);
        $this->assertNull($dto->body);
        $this->assertEquals(Carbon::parse('2021-12-12 15:00:00'), $dto->departure);
        $this->assertEquals(Carbon::parse('2021-12-12 15:32:45'), $dto->arrival);
        $this->assertEquals($origin, $dto->origin);
        $this->assertEquals($destination, $dto->destination);
        $this->assertNotEquals($dto->origin, $dto->destination);
        $this->assertEquals(Business::PRIVATE, $dto->travelReason);
        $this->assertEquals(StatusVisibility::PUBLIC, $dto->statusVisibility);
        $this->assertNull($dto->event);
    }

    public static function mixedIdentifierDataProvider(): array
    {
        return [
            'origin: IFOPT, destination Träwelling' => ['de-0815-1234:56:78', StationIdentifierType::IFOPT, 1234, true],
            'origin: RIL100, destination Träwelling' => ['RK', StationIdentifierType::DE_DB_RIL100, 2345, true],
            'origin: Motis, destination Träwelling' => ['foobar-baz', StationIdentifierType::MOTIS, 3456, true],
            'destination: IFOPT, origin Träwelling' => ['de-0815-1234:56:78', StationIdentifierType::IFOPT, 1234, false],
            'destination: RIL100, origin Träwelling' => ['RK', StationIdentifierType::DE_DB_RIL100, 2345, false],
            'destination: Motis, origin Träwelling' => ['foobar-baz', StationIdentifierType::MOTIS, 3456, false],
        ];
    }

    /**
     * @throws DataProviderException
     * @throws Exception
     */
    #[DataProvider('mixedIdentifierDataProvider')]
    public function test_hydrate_from_api_with_mixed_lookup_identifiers(string $identifier, StationIdentifierType $type, int $traewellingId, bool $firstIsIdentifier)
    {
        $origin = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $stationRepository = $this->mock(StationRepository::class);
        $tripRepository = $this->mock(TripRepository::class);
        $stationRepository->shouldReceive('getById')->once()->with($traewellingId)->andReturn($firstIsIdentifier ? $destination : $origin);
        $stationRepository->shouldReceive('getByIdentifier')->once()->with($identifier, $type)->andReturn($firstIsIdentifier ? $origin : $destination);
        $tripRepository->shouldReceive('getByIdentifier')->once();

        $data = [
            'business' => Business::PRIVATE->value,
            'visibility' => StatusVisibility::PUBLIC->value,
            'tripId' => '1234',
            'lineName' => 'ICE 123',
            'departure' => '2021-12-12 15:00:00',
            'arrival' => '2021-12-12 15:32:45',
        ];

        if ($firstIsIdentifier) {
            $data['destination'] = $traewellingId;
            $data['startIdentifier'] = $identifier;
            $data['startIdentifierType'] = $type->value;
        } else {
            $data['start'] = $traewellingId;
            $data['destinationIdentifier'] = $identifier;
            $data['destinationIdentifierType'] = $type->value;
        }

        $request = new CheckinRequest($data);

        $hydrator = new CheckinRequestHydrator(
            validated: $request,
            user: $this->user,
            dto: null,
            stationRepository: $stationRepository,
            eventRepository: null,
            tripRepository: $tripRepository
        );
        $dto = $hydrator->hydrateFromApi();

        $this->assertFalse($dto->postOnMastodonFlag);
        $this->assertFalse($dto->forceFlag);
        $this->assertFalse($dto->chainFlag);
        $this->assertNull($dto->body);
        $this->assertEquals(Carbon::parse('2021-12-12 15:00:00'), $dto->departure);
        $this->assertEquals(Carbon::parse('2021-12-12 15:32:45'), $dto->arrival);
        $this->assertEquals($origin, $dto->origin);
        $this->assertEquals($destination, $dto->destination);
        $this->assertNotEquals($dto->origin, $dto->destination);
        $this->assertEquals(Business::PRIVATE, $dto->travelReason);
        $this->assertEquals(StatusVisibility::PUBLIC, $dto->statusVisibility);
        $this->assertNull($dto->event);

    }
}
