<?php

namespace App\Http\Requests;

use App\Enum\StatusVisibility;
use Illuminate\Validation\Rules\Enum;

class UpdateStatusTagRequest extends StoreStatusTagRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['visibility'] = ['nullable', new Enum(StatusVisibility::class)];
        $rules['key'] = self::KEY_BASE_RULES;

        return $rules;
    }
}
