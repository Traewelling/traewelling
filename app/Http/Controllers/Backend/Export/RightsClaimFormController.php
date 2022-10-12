<?php

namespace App\Http\Controllers\Backend\Export;

use App\Models\Status;
use Illuminate\Support\Facades\Auth;

class RightsClaimFormController
{
    public function render(int $id) {
        $status = Status::findOrFail($id);

        if (Auth::user()->cannot('update', $status)) {
            abort(403);
        }

        $originStopover      = $status->trainCheckin->origin_stopover;
        $plannedDeparture    = $originStopover->departure_planned;
        $destinationStopover = $status->trainCheckin->destination_stopover;
        $plannedArrival      = $destinationStopover->arrival_planned;
        $realArrival         = $destinationStopover->arrival_real;

        $fields = [
            "S1F1" => $status->trainCheckin->departure->format("d"), // Reisedatum Tag (TT)
            "S1F2" => $status->trainCheckin->departure->format("m"), // Reisedatum Monat (MM)
            "S1F3" => $status->trainCheckin->departure->format("y"), // Reisedatum Jahr (JJ)

            "S1F4" => substr($originStopover->trainStation->name, 0, 26), // Startbahnhof
            "S1F5" => $plannedDeparture->format("H"), // Abfahrt laut Fahrplan Stunde (HH)
            "S1F6" => $plannedDeparture->format("i"), // Abfahrt laut Fahrplan Minute (MM)

            "S1F7" => substr($destinationStopover->trainStation->name, 0, 26), // Zielbahnhof
            "S1F8" => $plannedArrival->format("H"), // Ankunft laut Fahrplan Stunde (HH)
            "S1F9" => $plannedArrival->format("i"), // Ankunft laut Fahrplan Minute (MM)

            "S1F10" => $realArrival->format("d"),  // Ankunftsdatum Tag (TT)
            "S1F11" => $realArrival->format("m"),  // Ankunftsdatum Monat (MM)
            "S1F12" => $realArrival->format("y"),  // Ankunftsdatum Jahr (JJ)

            "S1F13" => substr(explode(" ", $status->trainCheckin->hafasTrip->linename)[0], 0, 3), // Zugart Angekommen mit
            "S1F14" => str_pad(explode(" ", $status->trainCheckin->hafasTrip->linename)[1], 5, " ", STR_PAD_LEFT), // Zugnummer Angekommener mit

            "S1F15" => $realArrival->format("H"), // Reale Ankunftszeit Stunde (HH)
            "S1F16" => $realArrival->format("i"), // Reale Ankunftszeit Minute (MM)

            "S1F17" => substr(explode(" ", $status->trainCheckin->hafasTrip->linename)[0], 0, 3), // Zugart Erster versp. / ausgefallener Zug
            "S1F18" => str_pad(explode(" ", $status->trainCheckin->hafasTrip->linename)[1], 5, " ", STR_PAD_LEFT), // Zugnummer Erster versp. / ausgefallener Zug

            "S1F19" => $plannedDeparture->format("H"), // Abfahrt laut Fahrplan Stunde (HH) Erster versp. / ausgefallener Zug
            "S1F20" => $plannedDeparture->format("i"), // Abfahrt laut Fahrplan Minute (MM) Erster versp. / ausgefallener Zug

            "S2F5"  => substr($status->user->name, 0, 18), // Vorname
            "S2F19" => substr($status->user->email ?? "", 0, 37), // Emailadresse
        ];

        $pdf = new \FPDM(resource_path('views/pdf/fahrgastrechteformular.pdf'));
        $pdf->Load($fields, true); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
        $pdf->Merge();
        $pdf->Output();
        exit;
    }
}
