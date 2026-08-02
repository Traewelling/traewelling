<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftTripStopoversRequest extends FormRequest
{
    /**
     * A year in minutes. Anything beyond that is hopefully a mistake.
     */
    private const int MAX_OFFSET_IN_MINUTES = 525600;

    public function rules(): array
    {
        return [
            'minutes' => ['required', 'integer', 'between:-' . self::MAX_OFFSET_IN_MINUTES . ',' . self::MAX_OFFSET_IN_MINUTES],
        ];
    }
}
