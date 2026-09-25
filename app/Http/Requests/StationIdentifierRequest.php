<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\StationIdentifierType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StationIdentifierRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(StationIdentifierType::class)],
            'identifier' => ['required', 'string', 'max:255'],
            'origin' => [
                Rule::requiredIf($this->input('type') === StationIdentifierType::LOCAL_CODE->value),
                Rule::prohibitedIf($this->input('type') !== StationIdentifierType::LOCAL_CODE->value),
                'nullable',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(_[a-z0-9]+)*$/',
            ],
        ];
    }

    public function identifierType(): StationIdentifierType
    {
        return StationIdentifierType::from($this->validated('type'));
    }
}
