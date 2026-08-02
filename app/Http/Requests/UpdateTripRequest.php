<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\HafasTravelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTripRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category' => ['sometimes', new Enum(HafasTravelType::class)],
            'lineName' => ['sometimes', 'string', 'max:255'],
            'journeyNumber' => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'operatorUuid' => ['sometimes', 'nullable', 'uuid', 'exists:operators,id'],
        ];
    }
}
