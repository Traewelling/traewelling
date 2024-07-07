<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\HafasException;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Event;
use App\Models\Station;
use App\Models\Stopover;
use App\Repositories\CheckinHydratorRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\MockObject\Exception;
use Tests\Unit\UnitTestCase;

class CheckinRequestHydratorTest extends UnitTestCase
{
    private Authenticatable $user;

    /**
     * @throws Exception
     */
    public function setUp(): void {
        parent::setUp();
        $this->user = $this->createMock(Authenticatable::class);
    }

    /**
     * @throws HafasException
     * @throws Exception
     * @throws \JsonException
     */
    public function testHydrateFromAdminWithFullArray() {
        $origin      = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $repository  = $this->mock(CheckinHydratorRepository::class);
        $repository->shouldReceive('getOneStation')->once()->andReturn($origin);

        $stopover = $this->mock(Stopover::class);
        $stopover->shouldReceive('getAttribute')->once()->with('station')->andReturn($destination);
        $stopover->shouldReceive('getAttribute')->once()->with('arrival_planned')->andReturn(Carbon::parse('2021-12-12 15:32:45'));
        $repository->shouldReceive('findOrFailStopover')->once()->with('4321')->andReturn($stopover);
        $repository->shouldReceive('findEvent')->never();
        $repository->shouldReceive('getHafasTrip')->once()->with('1234', 'ICE 123');


        $array = [
            'body'                => 'Test',
            'business'            => Business::BUSINESS->value,
            'visibility'          => StatusVisibility::PRIVATE->value,
            'eventId'             => null,
            'toot'                => false,
            'chainPost'           => false,
            'ibnr'                => false,
            'tripId'              => '1234',
            'lineName'            => 'ICE 123',
            'start'               => 1234,
            'destinationStopover' => 4321,
            'departure'           => '2021-12-12 15:00:00',
            'force'               => true
        ];

        $hydrator = new CheckinRequestHydrator($array, $this->user, null, $repository);
        $dto      = $hydrator->hydrateFromAdmin();

        $this->assertFalse($dto->postOnMastodonFlag);
        $this->assertTrue($dto->forceFlag);
        $this->assertFalse($dto->chainFlag);
        $this->assertEquals('Test', $dto->body);
        $this->assertEquals(Carbon::parse('2021-12-12 15:00:00'), $dto->departure);
        $this->assertEquals(Carbon::parse('2021-12-12 15:32:45'), $dto->arrival);
        $this->assertEquals($origin, $dto->origin);
        $this->assertEquals($destination, $dto->destination);
        $this->assertNotEquals($dto->origin, $dto->destination);
        $this->assertEquals(Business::BUSINESS, $dto->travelReason);
        $this->assertEquals(StatusVisibility::PRIVATE, $dto->statusVisibility);
        $this->assertNull($dto->event);
    }

    /**
     * @throws HafasException
     * @throws Exception
     */
    public function testHydrateFromApiWithFullArray() {
        $origin      = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $repository  = $this->mock(CheckinHydratorRepository::class);
        $repository->shouldReceive('getOneStation')->with('id', 4321)->andReturn($destination);
        $repository->shouldReceive('getOneStation')->with('id', 1234)->andReturn($origin);
        $repository->shouldReceive('getHafasTrip')->once();
        $repository->shouldReceive('findEvent')->never();


        $array = [
            'body'        => 'Test',
            'business'    => Business::PRIVATE->value,
            'visibility'  => StatusVisibility::PUBLIC->value,
            'eventId'     => null,
            'toot'        => false,
            'chainPost'   => true,
            'ibnr'        => false,
            'tripId'      => '1234',
            'lineName'    => 'ICE 123',
            'start'       => 1234,
            'destination' => 4321,
            'departure'   => '2021-12-12 15:00:00',
            'arrival'     => '2021-12-12 15:32:45',
            'force'       => false
        ];

        $hydrator = new CheckinRequestHydrator($array, $this->user, null, $repository);
        $dto      = $hydrator->hydrateFromApi();


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
     * @throws HafasException
     * @throws Exception
     */
    public function testHydrateFromApiWithNullableFields() {
        $origin      = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $repository  = $this->mock(CheckinHydratorRepository::class);
        $repository->shouldReceive('getOneStation')->with('id', 4321)->andReturn($destination);
        $repository->shouldReceive('getOneStation')->with('id', 1234)->andReturn($origin);
        $repository->shouldReceive('getHafasTrip')->once();
        $repository->shouldReceive('findEvent')->never();


        $array = [
            'business'    => Business::PRIVATE->value,
            'visibility'  => StatusVisibility::PUBLIC->value,
            'toot'        => false,
            'chainPost'   => true,
            'tripId'      => '1234',
            'lineName'    => 'ICE 123',
            'start'       => 1234,
            'destination' => 4321,
            'departure'   => '2021-12-12 15:00:00',
            'arrival'     => '2021-12-12 15:32:45',
        ];

        $hydrator = new CheckinRequestHydrator($array, $this->user, null, $repository);
        $dto      = $hydrator->hydrateFromApi();


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
     * @throws HafasException
     * @throws Exception
     */
    public function testHydrateFromApiWithEventAndForceId() {
        $origin      = $this->mock(Station::class);
        $destination = $this->createMock(Station::class);
        $event       = $this->createMock(Event::class);
        $repository  = $this->mock(CheckinHydratorRepository::class);
        $repository->shouldReceive('getOneStation')->with('ibnr', 4321)->andReturn($destination);
        $repository->shouldReceive('getOneStation')->with('ibnr', 1234)->andReturn($origin);
        $repository->shouldReceive('getHafasTrip')->once();
        $repository->shouldReceive('findEvent')->once()->with(123)->andReturn($event);


        $array = [
            'business'    => Business::PRIVATE->value,
            'visibility'  => StatusVisibility::PUBLIC->value,
            'toot'        => false,
            'chainPost'   => true,
            'tripId'      => '1234',
            'lineName'    => 'ICE 123',
            'start'       => 1234,
            'destination' => 4321,
            'departure'   => '2021-12-12 15:00:00',
            'arrival'     => '2021-12-12 15:32:45',
            'ibnr'        => true,
            'eventId'       => 123,
        ];

        $hydrator = new CheckinRequestHydrator($array, $this->user, null, $repository);
        $dto      = $hydrator->hydrateFromApi();


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
}
