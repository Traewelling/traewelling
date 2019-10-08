<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Session;
use Request;
use Config;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

            // Set default session language if none is set
            if(!Session::has('language'))
            {
                // detect browser language
                $headerlang = Request::server('http_accept_language');
                if(isset($headerlang))
                {
                    $headerlang = substr(Request::server('http_accept_language'), 0, 2);

                    if(array_key_exists($headerlang, Config::get('app.locales')))
                    {
                        // browser lang is supported, use it
                        $lang = $headerlang;
                    }
                    // use default application lang
                    else
                    {
                        $lang = Config::get('app.locale');
                    }
                }
                // use default
                else
                {
                    // use default application lang
                    $lang = Config::get('app.locale');
                }

                // set application language for that user
                Session::put('language', $lang);
                App::setLocale(Session::get('language'));
            }
            // session is available
            else
            {
                // set application to session lang
                App::setLocale(Session::get('language'));
            }


        return $next($request);
    }
}
