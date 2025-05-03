<?php

use App\DataProviders\Repositories\StationRepository;
use App\Models\Stopover;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTrainStopoversTable extends Migration
{
    public function up(): void {
        Schema::create('train_stopovers', function(Blueprint $table) {
            $table->id();

            $table->string('trip_id');
            $table->unsignedBigInteger('train_station_id');

            $table->timestamp('arrival_planned')->nullable();
            $table->timestamp('arrival_real')->nullable();

            $table->string('arrival_platform_planned')->nullable();
            $table->string('arrival_platform_real')->nullable();

            $table->timestamp('departure_planned')->nullable();
            $table->timestamp('departure_real')->nullable();

            $table->string('departure_platform_planned')->nullable();
            $table->string('departure_platform_real')->nullable();

            $table->timestamps();

            $table->unique(['trip_id', 'train_station_id']);

            $table->foreign('trip_id')
                  ->references('trip_id')
                  ->on('hafas_trips')
                  ->cascadeOnDelete();
            $table->foreign('train_station_id')
                  ->references('id')
                  ->on('train_stations')
                  ->cascadeOnUpdate();
        });

        DB::table('hafas_trips')->orderBy('id')
          ->whereNotNull('stopovers')
          ->chunk(100, function($hafasTrips) {
              foreach ($hafasTrips as $hafasTrip) {

                  $stopovers = json_decode($hafasTrip->stopovers);

                  foreach ($stopovers as $stopover) {

                      $hafasStop = StationRepository::parseHafasStopObject($stopover->stop);

                      Stopover::updateOrCreate(
                          [
                              'trip_id' => $hafasTrip->trip_id,
                                                                                                                                                                                                'train_station_id' => $hafasStop->id
                          ], [
                              'arrival_planned'            => $stopover?->plannedArrival,
                              'arrival_real'               => $stopover?->arrival,
                              'arrival_platform_planned'   => $stopover?->plannedArrivalPlatform,
                              'arrival_platform_real'      => $stopover?->arrivalPlatform,
                              'departure_planned'          => $stopover?->plannedDeparture,
                              'departure_real'             => $stopover?->departure,
                              'departure_platform_planned' => $stopover?->plannedDeparturePlatform,
                              'departure_platform_real'    => $stopover?->departurePlatform
                          ]
                      );
                  }

              }
          });
    }

    public function down(): void {
        Schema::dropIfExists('train_stopovers');
    }
}
