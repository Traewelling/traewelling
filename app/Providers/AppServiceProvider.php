<?php

namespace App\Providers;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Helpers\Lang;
use App\Http\Controllers\Backend\Auth\AuthorizationController;
use App\Http\Controllers\Backend\VersionController;
use App\Notifications\LangMailMessage;
use Carbon\CarbonInterval;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Socialite\Contracts\Factory;
use Revolution\Socialite\Mastodon\MastodonProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(AuthorizationController::class)
            ->needs(StatefulGuard::class)
            ->give(fn () => Auth::guard(config('passport.guard', null)));
        Passport::$clientUuids = false; // Preserve existing integer client IDs (legacy)
        Passport::ignoreCsrfToken();
        Passport::tokensExpireIn(CarbonInterval::minutes(60));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::days(90));

        $dataProvider = new DataProviderBuilder()->build();
        $this->app->instance(DataProviderInterface::class, $dataProvider);
    }

    /**
     * Bootstrap any application services.
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if (config('app.force-https')) {
            URL::forceScheme('https');
        }

        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'mastodon',
            function ($app) use ($socialite) {
                $config = $app['config']['services.mastodon'];

                return $socialite->buildProvider(MastodonProvider::class, $config);
            }
        );

        Paginator::useBootstrap();

        Blade::if('admin', static function (): bool {
            return auth()->user()?->hasRole('admin');
        });

        Http::globalRequestMiddleware(fn ($request) => $request->withHeader('User-Agent', VersionController::getUserAgent()));

        $this->registerMails();
    }

    private function registerMails(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $locale = $notifiable->language ?? 'en';

            $mail = new LangMailMessage($locale);
            if ($notifiable->username) {
                $mail->greeting(Lang::trans(key: 'mail.hello', replace: ['username' => $notifiable->username], locale: $locale));
            }

            $mail->subject(Lang::trans(key: 'mail.reset_password.subject', locale: $locale))
                ->line(Lang::trans(key: 'mail.reset_password.line1', locale: $locale))
                ->action(
                    Lang::trans(key: 'mail.reset_password.action', locale: $locale),
                    url(route('password.reset', [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ], false)))
                ->line(Lang::trans(
                    key: 'mail.reset_password.line2',
                    replace: ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')],
                    locale: $locale
                ))
                ->line(Lang::trans(key: 'mail.reset_password.line3', locale: $locale))
                ->locale($locale);

            return $mail;
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $locale = $notifiable->language ?? 'en';

            $mail = new LangMailMessage($locale);
            if ($notifiable->username) {
                $mail->greeting(Lang::trans(key: 'mail.hello', replace: ['username' => $notifiable->username], locale: $locale));
            }
            $mail->subject(Lang::trans(key: 'mail.verify_email.subject', locale: $locale))
                ->line(Lang::trans(key: 'mail.verify_email.line1', locale: $locale))
                ->action(Lang::trans(key: 'mail.verify_email.action', locale: $locale), $url);

            return $mail;
        });
    }
}
