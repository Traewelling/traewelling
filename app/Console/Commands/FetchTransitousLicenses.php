<?php

namespace App\Console\Commands;

use App\Models\MotisSourceLicense;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTransitousLicenses extends Command
{
    private const string LICENSE_URL = 'https://api.transitous.org/gtfs/license.json';
    private const string PATH = 'tmp/transitous';

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
        if (empty($response) || $response->status() !== 200) {
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
            $country = $license['region_code'];
            $spdx = $license['spdx_license_identifier'] ?? $license['rt_spdx_license_identifier'] ?? '';
            $licenseUrl = $license['license_url'] ?? '';
            $name = $license['filename'];
            $humanName = $license['human_name'] ?? null;
            $source = $license['source_url'] ?? '';
            $active = array_key_exists($spdx, MotisSourceLicense::SPDX);

            $this->info(
                sprintf('[%s] Found license: %s (%s) %s', $country, $name, $spdx, $active ? 'active' : 'inactive')
            );

            MotisSourceLicense::updateOrCreate(
                [
                    'provider' => 'transitous',
                    'country' => $country,
                    'name' => $name,
                ],
                [
                    'license_url' => $licenseUrl,
                    'source_url' => $source,
                    'spdx' => $spdx,
                    'human_name' => $humanName,
                    'active' => $active,
                ]
            );
        }

        return self::SUCCESS;
    }
}
