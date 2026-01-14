<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $license_id
 * @property string[] $source_ids
 */
class MassAssignSourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'license_id' => 'required|exists:licenses,id',
            'source_ids' => 'required|array',
        ];
    }
}
