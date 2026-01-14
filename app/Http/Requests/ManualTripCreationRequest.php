<?php

namespace App\Http\Requests;

use App\Enum\HafasTravelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ManualTripCreationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (auth()->user()?->can('disallow-manual-trips')) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', new Enum(HafasTravelType::class)],
            'lineName' => ['required'],
            'journeyNumber' => ['nullable', 'numeric', 'min:1'],
            'operatorId' => ['nullable', 'numeric', 'exists:hafas_operators,id'],
            'originId' => ['required', 'exists:train_stations,id'],
            'originDeparturePlanned' => ['required', 'date'],
            'originDepartureReal' => ['nullable', 'date'],
            'destinationId' => ['required', 'exists:train_stations,id'],
            'destinationArrivalPlanned' => ['required', 'date'],
            'destinationArrivalReal' => ['nullable', 'date'],
            'stopovers.*.stationId' => ['required', 'exists:train_stations,id'],
            'stopovers.*.arrival' => ['required_without:stopovers.*.departure', 'required_with:stopovers.*.arrivalReal', 'date'],
            'stopovers.*.arrivalReal' => ['nullable', 'date'],
            'stopovers.*.departure' => ['required_without:stopovers.*.arrival,null', 'required_with:stopovers.*.departureReal', 'date'],
            'stopovers.*.departureReal' => ['nullable', 'date'],
        ];
    }
}
