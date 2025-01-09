<?php

use Illuminate\Support\Facades\Log;

/**
 * Translate the given message.
 * Overloads the laravel own helper method
 *
 * @param string|null $key
 * @param array       $replace
 * @param string|null $locale
 *
 * @return string|array|null
 */
function __(?string $key = null, array $replace = [], ?string $locale = null) {
    if (is_null($key)) {
        return $key;
    }

    $translation = trans($key, $replace, $locale);
    if ($translation !== $key) {
        return $translation;
    }

    //if no translation for the current language is found try english...
    $translation = trans($key, $replace, 'en');
    if ($translation !== $key) {
        return $translation;
    }

    //if no translation for the current language is found try german...
    $translation = trans($key, $replace, 'de');
    if ($translation !== $key) {
        return $translation;
    }

    //What? Why we don't have these translation in german? This is our main language.
    //When we are reaching this line something is broken. Please fix it.
    Log::warning('Missing translation for key: ' . $key);
    //But nevermind, first return the key:
    return trans($key, $replace, $locale);
}

/**
 * Overriding the laravel own helper method, so we can handle a correct fallback
 *
 * @param       $key
 * @param       $number
 * @param array $replace
 * @param       $locale
 *
 * @return mixed
 */
function trans_choice($key, $number, array $replace = [], $locale = null) {
    $translation = app('translator')->choice($key, $number, $replace, $locale);
    if ($translation !== $key) {
        return $translation;
    }
    //if no translation for the current language is found try english...
    $translation = app('translator')->choice($key, $number, $replace, 'en');
    if ($translation !== $key) {
        return $translation;
    }
    //if no translation for the current language is found try german...
    $translation = app('translator')->choice($key, $number, $replace, 'de');
    if ($translation !== $key) {
        return $translation;
    }
    //What? Why we don't have these translation in german? This is our main language.
    //When we are reaching this line something is broken. Please fix it.
    //But nevermind, then return the key:
    return $translation;
}
