<?php

namespace App\DataProviders\Repositories;

use App\Enum\DataProvider;
use App\Models\MotisSourceLicense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class MotisLicenseRepository
{
    public function getActiveLicense(string $gtfsSource, DataProvider $source): ?MotisSourceLicense
    {
        $query = $this->licenseQuery($gtfsSource, $source);

        return $query?->where('active', true)->first();
    }

    private function licenseQuery(string $gtfsSource, DataProvider $source): ?Builder
    {
        [$country, $name] = $this->getCountryAndLicense($gtfsSource);
        if (empty($name) || empty($country)) {
            Log::warning('no matching license format found in ' . $gtfsSource);

            return null;
        }

        return MotisSourceLicense::where([
            'provider' => $source->value,
            'country' => strtoupper($country),
            'name' => $name . '.zip',
        ]);
    }

    public function getLicense(string $gtfsSource, DataProvider $source): ?MotisSourceLicense
    {
        $query = $this->licenseQuery($gtfsSource, $source);

        return $query?->first();
    }

    #[ArrayShape(['country' => 'string', 'name' => 'string'])]
    public function getCountryAndLicense(string $source): array
    {
        $matches = [];
        preg_match('/^(?<name>(?<country>[a-zA-Z]+(?:-[a-zA-Z0-9]+)*)_.*?(?:\.gtfs|\.netex))(?:\.zip)?(?:\/.*)?$/', $source, $matches);
        $name = $matches['name'] ?? '';
        $country = $matches['country'] ?? '';

        return [$country, $name];
    }
}
