<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Revolution\Socialite\Mastodon\MastodonProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'mastodon',
            function ($app) use ($socialite) {
                $config = $app['config']['services.mastodon'];
                return $socialite->buildProvider(MastodonProvider::class, $config);
            }
        );

        Paginator::useBootstrap();
    }
}
