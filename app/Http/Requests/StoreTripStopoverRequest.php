<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripStopoverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'stationUuid' => ['required', 'uuid', 'exists:train_stations,uuid'],
            'arrivalPlanned' => ['nullable', 'date', 'required_without:departurePlanned'],
            'departurePlanned' => ['nullable', 'date', 'required_without:arrivalPlanned'],
            'arrivalReal' => ['nullable', 'date'],
            'departureReal' => ['nullable', 'date'],
            'arrivalPlatformPlanned' => ['nullable', 'string', 'max:255'],
            'departurePlatformPlanned' => ['nullable', 'string', 'max:255'],
            'arrivalPlatformReal' => ['nullable', 'string', 'max:255'],
            'departurePlatformReal' => ['nullable', 'string', 'max:255'],
            'cancelled' => ['sometimes', 'boolean'],
        ];
    }
}
