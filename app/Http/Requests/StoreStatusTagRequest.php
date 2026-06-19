<?php

namespace App\Http\Requests;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreStatusTagRequest extends FormRequest
{
    private ?array $allowedValues = null;

    protected const array KEY_BASE_RULES = ['string', 'max:255', 'regex:/^\w[^\/\n\r%?\\<>]*$/'];

    protected function prepareForValidation(): void
    {
        $urlKey = $this->route()->parameter('tagKey') ?? $this->key;

        if (str_starts_with($urlKey, 'trwl:')) {
            $statusTagKey = StatusTagKey::tryFrom($urlKey);

            if ($statusTagKey === null) {
                $this->failedValidation(new Validator(app('translator'), [], [])->after(function ($validator) use ($urlKey) {
                    $validator->errors()->add('key', __('validation.tag', ['value' => $urlKey]));
                }));
            }

            $this->allowedValues = $statusTagKey?->allowedValues();
        }
    }

    public function rules(): array
    {
        return [
            'key' => array_merge(['required'], self::KEY_BASE_RULES),
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::when($this->allowedValues !== null, [Rule::in($this->allowedValues ?? [])]),
            ],
            'visibility' => ['required', new Enum(StatusVisibility::class)],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $exception = $validator->getException();

        throw (new $exception($validator))
            ->errorBag($this->errorBag)
            ->status(400) // when the api was created / documented, the validator failed with 400 instead of 422. Keeping this due to compatibility reasons
            ->redirectTo($this->getRedirectUrl());
    }
}
