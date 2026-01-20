<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLicenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'human_name' => 'nullable|string|max:255',
            'attribution' => 'nullable|string|max:255',
            'license_url' => 'nullable|url|max:255',
            'source_url' => 'nullable|url|max:255',
            'spdx' => 'nullable|string|max:255',
            'automatically_activate_source' => 'required|boolean',
        ];
    }
}
