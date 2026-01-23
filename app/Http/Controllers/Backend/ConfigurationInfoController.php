<?php

namespace App\Http\Controllers\Backend;

use App\Dto\ConfigurationInformation\ConfigurationFeature;
use App\Dto\ConfigurationInformation\ConfigurationInformation;
use App\Dto\ConfigurationInformation\Language;
use App\Enum\ConfigurationFeatureEnum;
use App\Http\Controllers\Controller;

class ConfigurationInfoController extends Controller
{
    public function getConfigurationInfo(): ConfigurationInformation
    {
        return new ConfigurationInformation(
            appName: config('app.name'),
            appDebug: config('app.debug'),
            appUrl: config('app.url'),
            features: $this->getFeatures(),
            languages: $this->getLanguages(),
        );
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
