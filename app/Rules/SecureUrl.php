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
        if (str_starts_with($value, 'https://')) {
            return;
        }

        if (App::environment('local') || $this->isLoopbackUrl($value)) {
            return;
        }

        $fail(':attribute must be a secure URL.');
    }

    /**
     * RFC 8252 Nr. 8.3
     */
    private function isLoopbackUrl(string $value): bool
    {
        if (!str_starts_with($value, 'http://')) {
            return false;
        }

        $host = parse_url($value, PHP_URL_HOST);
        if ($host === null || $host === false) {
            return false;
        }

        $host = trim($host, '[]');

        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            return true;
        }

        if ($host === '::1') {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return str_starts_with($host, '127.');
        }

        return false;
    }
}
