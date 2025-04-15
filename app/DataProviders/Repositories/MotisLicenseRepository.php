<?php

namespace App\DataProviders\Repositories;

use App\Enum\DataProvider;
use App\Models\MotisSourceLicense;
use Illuminate\Support\Facades\Log;

class MotisLicenseRepository
{
    public function getLicense(string $gtfsSource, DataProvider $source): ?MotisSourceLicense {
        $matches = [];
        preg_match('/(?<name>(?<country>.*)_.*\.gtfs)/', $gtfsSource, $matches);
        $name    = $matches['name'] ?? '';
        $country = $matches['country'] ?? '';
        if (empty($name) || empty($country)) {
            Log::error('no matching license format found in ' . $gtfsSource);
            return null;
        }

        return MotisSourceLicense::where([
                                             'provider' => $source->value,
                                             'country'  => $country,
                                             'name'     => $name . '.zip',
                                             'active'   => true
                                         ])
                                 ->first();
    }
}
