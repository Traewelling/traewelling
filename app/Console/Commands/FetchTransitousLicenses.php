<?php

namespace App\Console\Commands;

use App\Models\MotisSourceLicense;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTransitousLicenses extends Command
{
    private const string LICENSE_URL = 'https://api.transitous.org/gtfs/license.json';

    protected $signature = 'trwl:fetch-transitous-licenses';

    protected $description = 'Fetch License data from transitous repository';

    public function handle(): int
    {
        return $this->parseLicenses();
    }

    private function getLicenseJson(): array
    {
        $this->info('Downloading licenses...');
        $response = Http::get(self::LICENSE_URL);
        if (!$response->successful()) {
            $this->error('Failed to download licenses');

            return [];
        }
        try {
            return json_decode($response->body(), true);
        } catch (Exception $e) {
            $this->error('Failed to decode licenses: ' . $e->getMessage());

            return [];
        }
    }

    private function parseLicenses(): int
    {
        $this->info('Reading licenses...');
        $licenses = $this->getLicenseJson();

        foreach ($licenses as $license) {
            $country = $license['country_code'] ?? null;
            $subdivision = $license['subdivision_code'] ?? null;
            if (empty($country)) {
                $this->error('No country code found for license: ' . json_encode($license));

                continue;
            }

            if (!empty($subdivision)) {
                if (!str_starts_with($subdivision, $country . '-')) {
                    $this->warn(
                        sprintf(
                            'Subdivision code "%s" does not start with country code "%s-". Adjusting...',
                            $subdivision,
                            $country
                        )
                    );
                    $country = $country . '-' . $subdivision;
                }
                $country = $subdivision;
            }

            $spdx = $license['spdx_license_identifier'] ?? $license['rt_spdx_license_identifier'] ?? '';
            $licenseUrl = $license['license_url'] ?? '';
            $name = $license['filename'];
            $humanName = $license['publisher']['name'] ?? $license['human_name'] ?? null;
            $source = $license['publisher']['url'] ?? $license['source_url'] ?? $license['source'] ?? null;
            $active = array_key_exists($spdx, MotisSourceLicense::SPDX);

            $this->info(
                sprintf('[%s] Found license: %s (%s) %s', $country, $name, $spdx, $active ? 'active' : 'inactive')
            );

            $where = [
                'provider' => 'transitous',
                'country' => $country,
                'name' => $name,
            ];
            $dbSource = MotisSourceLicense::with('manualLicense')->where($where)->first();

            /** @var \App\Models\License|null $manualLicense */
            $manualLicense = $dbSource?->manualLicense;
            $forceActive = $manualLicense?->automatically_activate_source ?? $dbSource?->force_active ?? false;

            $payload = [
                'license_url' => $licenseUrl,
                'source_url' => $source,
                'spdx' => $spdx,
                'human_name' => $humanName,
                'active' => $forceActive || $active,
            ];

            MotisSourceLicense::updateOrCreate($where, $payload);
        }

        return self::SUCCESS;
    }
}
