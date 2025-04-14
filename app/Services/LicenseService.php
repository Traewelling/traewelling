<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\LicenseDto;
use App\Models\License;
use App\Models\MotisSourceLicense;
use App\Models\Status;

class LicenseService
{
    public function getLicenseData(Status $status): LicenseDto {
        $status->load('checkin.trip.motisSourceLicense');

        if ($status->checkin->trip->motisSourceLicense->manualLicense) {
            return $this->getManualLicenseData(
                $status->checkin->trip->motisSourceLicense->manualLicense,
                $status->checkin->trip->motisSourceLicense
            );
        } else {
            return $this->getDefaultLicenseData($status->checkin->trip->motisSourceLicense);
        }
    }

    public function getManualLicenseData(License $license, MotisSourceLicense $source): LicenseDto {
        $licenseName = $license->name;
        $dataOrigin  = $license->human_name ?? $license->name;
        $provider    = $source->provider;

        $attributionString =
            __('license.provided', ['provider' => $provider, 'source' => $dataOrigin, 'license' => $licenseName]);

        return new LicenseDto(
            $licenseName ?? '',
            $license->attribution ?? $attributionString,
            $license->license_url,
            $source->source_url
        );
    }

    public function getDefaultLicenseData(MotisSourceLicense $license): LicenseDto {
        $spdx       = MotisSourceLicense::SPDX[$license->spdx];
        $dataOrigin = $license->human_name ?? $license->name;
        $provider   = $license->provider;

        return new LicenseDto(
            $spdx['name'],
            __('license.provided', ['provider' => $provider, 'source' => $dataOrigin, 'license' => $spdx['name']]),
            $spdx['url'],
            $license->source_url
        );
    }
}
