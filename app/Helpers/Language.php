<?php

use App\Helpers\Lang;

/**
 * Translate the given message.
 * Overloads the laravel own helper method
 *
 *
 * @return string|array|null
 */
function __(?string $key = null, array $replace = [], ?string $locale = null)
{
    return Lang::trans($key, $replace, $locale);
}

/**
 * Overriding the laravel own helper method, so we can handle a correct fallback
 *
 *
 * @return mixed
 */
function trans_choice($key, $number, array $replace = [], $locale = null)
{
    return Lang::trans_choice($key, $number, $replace, $locale);
}
