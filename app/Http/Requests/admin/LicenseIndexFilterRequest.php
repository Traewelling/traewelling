<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property ?string $country
 * @property ?string $name
 * @property ?string $human_name
 * @property ?bool $active
 */
class LicenseIndexFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'country' => 'string|nullable',
            'name' => 'string|nullable',
            'human_name' => 'string|nullable',
            'active' => 'boolean|nullable',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        return array_filter($validated, function ($value) {
            return !is_null($value);
        });
    }
}
