<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $identifier
 * @property string $identifier_provider
 */
class FindStationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'identifier_provider' => ['required', 'string', Rule::in(['transitous', 'ibnr'])],
        ];
    }
}
