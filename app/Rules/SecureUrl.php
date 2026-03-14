<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\App;
use Illuminate\Translation\PotentiallyTranslatedString;

class SecureUrl implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (!str_starts_with($value, 'https://') && !App::environment('local')) {
            $fail(':attribute must be a secure URL.');
        }
    }
}
