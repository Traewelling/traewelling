<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\LicenseDto;
use App\Models\License;
use App\Models\MotisSourceLicense;
use App\Models\Status;

class LicenseService
{
    public function getLicenseDataForStatus(Status $status): ?LicenseDto
    {
        $status->load('checkin.trip.motisSourceLicense.manualLicense');
        $manual = $status?->checkin->trip?->motisSourceLicense?->manualLicense;
        $source = $status?->checkin->trip?->motisSourceLicense;

        return $this->getLicenseData($manual, $source);
    }

    public function getLicenseDataForSource(MotisSourceLicense $source): ?LicenseDto
    {
        $source->load('manualLicense');
        $manual = $source->manualLicense;

        return $this->getLicenseData($manual, $source);
    }

    private function getManualLicenseData(License $license, MotisSourceLicense $source): LicenseDto
    {
        $licenseName = $license->name;
        $dataOrigin = $source->human_name ?? $license->human_name ?? $license->name;
        $provider = $source->provider;

        $attributionString =
            __('license.provided', ['provider' => $provider, 'source' => $dataOrigin, 'license' => $licenseName]);

        return new LicenseDto(
            $licenseName ?? '',
            $license->attribution ? $attributionString . ' – ' . $license->attribution : $attributionString,
            $license->license_url,
            $source->source_url
        );
    }

    private function getDefaultLicenseData(MotisSourceLicense $license, ?string $spdxIdentifier = null): LicenseDto
    {
        $spdx = $spdxIdentifier ? MotisSourceLicense::SPDX[$spdxIdentifier] : MotisSourceLicense::SPDX[$license->spdx];
        $dataOrigin = $license->human_name ?? $license->name;
        $provider = $license->provider;
        $attribution = __('license.provided', ['provider' => $provider, 'source' => $dataOrigin, 'license' => $spdx['name']]);

        if ($spdx['attribution'] ?? false) {
            $attribution = strtr(
                $spdx['attribution'],
                [
                    ':source' => $dataOrigin,
                ]
            );
        }

        return new LicenseDto(
            $spdx['name'],
            $attribution,
            $spdx['url'],
            $license->source_url
        );
    }

    private function getAttributionTextLicenseData(MotisSourceLicense $source): LicenseDto
    {
        $spdx = MotisSourceLicense::SPDX[$source->spdx] ?? null;

        return new LicenseDto(
            $spdx['name'] ?? $source->spdx ?? '',
            $source->attribution_text,
            $spdx['url'] ?? $source->license_url,
            $source->source_url
        );
    }

    private function getLicenseData(?License $manual, ?MotisSourceLicense $source): ?LicenseDto
    {
        if ($manual) {
            if (in_array($manual?->spdx, MotisSourceLicense::SPDX)) {
                return $this->getDefaultLicenseData($source, $manual?->spdx);
            }

            return $this->getManualLicenseData($manual, $source);
        }

        if ($source?->attribution_text) {
            return $this->getAttributionTextLicenseData($source);
        }

        if ($source?->spdx) {
            return $this->getDefaultLicenseData($source);
        }

        return null;
    }
}
