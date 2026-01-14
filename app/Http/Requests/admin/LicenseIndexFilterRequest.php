<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property ?string $name
 * @property ?string $human_name
 * @property ?bool $automatically_activate_source
 */
class LicenseIndexFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string|nullable',
            'human_name' => 'string|nullable',
            'automatically_activate_source' => 'boolean|nullable',
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
