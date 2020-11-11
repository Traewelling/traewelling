<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {

        // Set default session language if none is set
        if (!Session::has('language')) {
            // detect browser language
            $headerLang = Request::server('http_accept_language');
            if (isset($headerLang)) {
                $headerLang = substr(Request::server('http_accept_language'), 0, 2);

                if (array_key_exists($headerLang, Config::get('app.locales'))) {
                    // browser lang is supported, use it
                    $lang = $headerLang;
                } // use default application lang
                else {
                    $lang = Config::get('app.locale');
                }
            } // use default
            else {
                // use default application lang
                $lang = Config::get('app.locale');
            }

            // set application language for that user
            Session::put('language', $lang);
            App::setLocale(Session::get('language'));
        } // session is available
        else {
            // set application to session lang
            App::setLocale(Session::get('language'));
        }


        return $next($request);
    }
}
