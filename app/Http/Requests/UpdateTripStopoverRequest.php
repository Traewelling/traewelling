<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripStopoverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'stationUuid' => ['sometimes', 'uuid', 'exists:train_stations,uuid'],
            'arrivalPlanned' => ['sometimes', 'nullable', 'date'],
            'departurePlanned' => ['sometimes', 'nullable', 'date'],
            'arrivalReal' => ['sometimes', 'nullable', 'date'],
            'departureReal' => ['sometimes', 'nullable', 'date'],
            'arrivalPlatformPlanned' => ['sometimes', 'nullable', 'string', 'max:255'],
            'departurePlatformPlanned' => ['sometimes', 'nullable', 'string', 'max:255'],
            'arrivalPlatformReal' => ['sometimes', 'nullable', 'string', 'max:255'],
            'departurePlatformReal' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cancelled' => ['sometimes', 'boolean'],
        ];
    }
}
