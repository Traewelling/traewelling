<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TrainStation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusEditController extends Controller
{
    public function renderMain(): View {
        return view('admin.status.main');
    }

    public function renderEdit(Request $request): View {
        $validated = $request->validate([
                                            'statusId' => ['required', 'exists:statuses,id'],
                                        ]);

        return view('admin.status.edit', [
            'status' => Status::find($validated['statusId'])
        ]);
    }

    public function edit(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'statusId'    => ['required', 'exists:statuses,id'],
                                            'origin'      => ['required', 'exists:train_stations,id'],
                                            'destination' => ['required', 'exists:train_stations,id'],
                                            'body'        => ['nullable', 'string'],
                                        ]);

        $status = Status::find($validated['statusId']);

        $originStation      = TrainStation::find($validated['origin']);
        $destinationStation = TrainStation::find($validated['destination']);

        $newOrigin      = $status->trainCheckIn->HafasTrip->stopoversNew->where('train_station_id', $originStation->id)->first();
        $newDestination = $status->trainCheckIn->HafasTrip->stopoversNew->where('train_station_id', $destinationStation->id)->first();

        $status->trainCheckIn->update([
                                          'origin'      => $originStation->ibnr,
                                          'destination' => $destinationStation->ibnr,
                                          'departure'   => $newOrigin->departure->toIso8601String(),
                                          'arrival'     => $newDestination->arrival->toIso8601String(),
                                      ]);

        if ($status->body != $validated['body']) {
            $status->update(['body' => $validated['body']]);
        }

        return redirect()->route('admin.status')->with('alert-success', 'Der Status wurde bearbeitet.');
    }

}
