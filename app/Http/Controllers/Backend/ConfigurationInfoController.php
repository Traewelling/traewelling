<?php

namespace App\Http\Controllers\Backend;

use App\Dto\ConfigurationInformation\ConfigurationFeature;
use App\Dto\ConfigurationInformation\ConfigurationInformation;
use App\Dto\ConfigurationInformation\Language;
use App\Enum\ConfigurationFeatureEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ConfigurationInfoController extends Controller
{
    public function getConfigurationInfo(): ConfigurationInformation
    {
        return Cache::remember('app_configuration_info', 3600, fn () => new ConfigurationInformation(
            appName: config('app.name'),
            appDebug: config('app.debug'),
            appUrl: config('app.url'),
            version: VersionController::getVersion() ?: 'unknown',
            gdprExportCooldown: config('trwl.gdpr_export.days'),
            features: $this->getFeatures(),
            languages: $this->getLanguages(),
        ));
    }

    private function getFeatures(): array
    {
        return [
            new ConfigurationFeature(
                name: ConfigurationFeatureEnum::USER_REGISTRATION,
                enabled: config('app.registration.enabled'),
            ),
            new ConfigurationFeature(
                name: ConfigurationFeatureEnum::YEAR_IN_REVIEW,
                enabled: config('trwl.year_in_review.alert'),
            ),
        ];
    }

    private function getLanguages(): array
    {
        $languages = config('app.locales');

        return array_map(
            fn ($code, $name) => new Language(code: $code, name: $name),
            array_keys($languages),
            array_values($languages)
        );
    }
}
