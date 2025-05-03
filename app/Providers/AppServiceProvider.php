<?php

namespace App\Providers;

use App\Http\Controllers\Backend\Auth\AuthorizationController;
use App\Models\HafasOperator;
use App\Policies\OperatorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
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
        $this->app->when(AuthorizationController::class)
                  ->needs(StatefulGuard::class)
                  ->give(fn() => Auth::guard(config('passport.guard', null)));
        Passport::ignoreCsrfToken();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void {
        if (config('app.force-https')) {
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

        Blade::if("admin", static function(): bool {
            return auth()->user()?->hasRole('admin');
        });

        Gate::policy(HafasOperator::class, OperatorPolicy::class); // TODO: remove after HafasOperator Model is renamed to Operator (then no need for this manual policy registration)
    }
}
