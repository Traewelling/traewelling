<?php

use App\Models\StopoverVehicleSequence;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void {
        DB::table('carriage_sequences')->orderBy('id')->chunk(100, function($carriageSequences) {
            foreach ($carriageSequences as $carriageSequence) {
                $vehicle = Vehicle::updateOrCreate(
                    [
                        'name' => $carriageSequence->vehicle_number
                    ],
                    [
                        'classification' => $carriageSequence->vehicle_type,
                        'created_at'     => $carriageSequence->created_at,
                        'updated_at'     => $carriageSequence->updated_at,
                    ]
                );

                StopoverVehicleSequence::updateOrCreate(
                    [
                        'stopover_id' => $carriageSequence->stopover_id,
                        'position'    => $carriageSequence->position,
                    ], [
                        'sequence'     => $carriageSequence->sequence,
                        'vehicle_id'   => $vehicle->id,
                        'order_number' => $carriageSequence->order_number,
                    ]
                );
            }
        });
    }

    public function down(): void {

    }
};
