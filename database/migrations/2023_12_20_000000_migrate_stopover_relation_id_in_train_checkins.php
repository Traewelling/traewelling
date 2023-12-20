<?php

use App\Models\TrainCheckin;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void {
        while (TrainCheckin::whereNull('origin_stopover_id')->orWhereNull('destination_stopover_id')->count() > 0) {
            TrainCheckin::with(['HafasTrip.stopovers', 'originStation', 'destinationStation'])
                        ->whereNull('origin_stopover_id')
                        ->orWhereNull('destination_stopover_id')
                        ->limit(100)
                        ->each(function(TrainCheckin $checkin) {
                            $originStopover = $checkin->HafasTrip->stopovers->where('train_station_id', $checkin->originStation->id)
                                                                            ->where('departure_planned', $checkin->departure)
                                                                            ->first();

                            $destinationStopover = $checkin->HafasTrip->stopovers->where('train_station_id', $checkin->destinationStation->id)
                                                                                 ->where('arrival_planned', $checkin->arrival)
                                                                                 ->first();

                            if ($originStopover === null) {
                                echo "ERROR: Could not find origin stopover for checkin {$checkin->id}\n";
                                return;
                            }
                            if ($destinationStopover === null) {
                                echo "ERROR: Could not find destination stopover for checkin {$checkin->id}\n";
                                return;
                            }

                            $checkin->update([
                                                 'origin_stopover_id'      => $originStopover->id,
                                                 'destination_stopover_id' => $destinationStopover->id,
                                             ]);
                            echo ".";
                        });
        }
    }
};
