<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Http\Request;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {

        if ($request->has('language')) {

            if (!self::isValidLanguageCode($request->get('language'))) {
                return $next($request);
            }

            //update language setting for user
            if (auth()->check()) {
                auth()->user()->update([
                                           'language' => $request->get('language')
                                       ]);
            }

            //update language for current session
            app()->setLocale($request->get('language'));
            return $next($request);
        }

        //If the user has set a language use it for the call
        if (auth()->check() && auth()->user()->language != null) {
            app()->setLocale(auth()->user()->language);
            return $next($request);
        }

        //If the session has set a language use it for the call
        if (session()->has('language')) {
            app()->setLocale(session()->get('language'));
            return $next($request);
        }

        // Use default session language if none is set -> detect browser language
        $browserLanguage = request()->server('http_accept_language');
        if (isset($browserLanguage)) {
            $browserLanguage = substr(request()->server('http_accept_language'), 0, 2);

            if (self::isValidLanguageCode($browserLanguage)) {
                // browser lang is supported, use it
                app()->setLocale($browserLanguage);
            } else {
                //if browser lang is NOT supported, we should use english so every user can understand
                app()->setLocale('en');
            }
            return $next($request);
        }

        // use default application lang
        app()->setLocale(config('app.locale'));
        return $next($request);
    }

    private static function isValidLanguageCode(string $langCode): bool {
        return array_key_exists($langCode, config('app.locales'));
    }
}
