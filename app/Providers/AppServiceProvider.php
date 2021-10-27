<?php

namespace App\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use Revolution\Socialite\Mastodon\MastodonProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void {
        if (env('APP_ENV') === 'prod') {
            URL::forceScheme('https');
        }
        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'mastodon',
            function($app) use ($socialite) {
                $config = $app['config']['services.mastodon'];
                return $socialite->buildProvider(MastodonProvider::class, $config);
            }
        );

        Paginator::useBootstrap();
    }
}
