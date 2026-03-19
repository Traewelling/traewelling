<?php

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\License;
use App\Models\MotisSourceLicense;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchManualTransitousLicenses implements ShouldQueue
{
    use Queueable;

    private const string LICENSE_URL = 'https://raw.githubusercontent.com/Traewelling/transitous-licenses/refs/heads/main/licenses.json';

    public function __construct()
    {
        $this->onQueue(Queue::BACKGROUND->value);
    }

    public function handle(): void
    {
        $data = $this->getLicenseJson();
        if (empty($data)) {
            Log::error('No license data found, skipping manual license import');

            return;
        }

        $this->parseManualLicenses($data['proprietary_licenses']);
        $this->parseSources($data['sources']);
    }

    private function parseManualLicenses(array $licenses): void
    {
        foreach ($licenses as $license) {
            try {
                License::updateOrCreate(
                    ['name' => $license['identifier']],
                    [
                        'human_name' => $license['name'] ?? $license['identifier'],
                        'attribution' => $license['attribution_text'] ?? null,
                        'license_url' => $license['url'] ?? null,
                        'automatically_activate_source' => true,
                    ]
                );
                Log::info('Created license: ' . $license['identifier']);
            } catch (Exception $e) {
                Log::error('Failed to create license: ' . $e->getMessage());
            }
        }
    }

    private function parseSources(array $sources): void
    {
        foreach ($sources as $source) {
            try {
                if ($source['custom_license']) {
                    $license = License::where('name', $source['custom_license'])->first();
                    if (!$license) {
                        Log::error('Custom license not found for source: ' . $source['file'] . ', license: ' . $source['custom_license']);

                        continue;
                    }

                    $motisSource = MotisSourceLicense::where('name', $source['file'])->first();
                    if (!$motisSource) {
                        Log::error('Motis source not found for source: ' . $source['file']);

                        continue;
                    }

                    $motisSource->update(['license_id' => $license->id, 'active' => $license->automatically_activate_source]);
                    Log::debug('Updated source with custom license: ' . $source['file'] . ' -> ' . $license->name);
                } elseif ($source['spdx']) {
                    $motisSource = MotisSourceLicense::where('name', $source['file'])->first();
                    if (!$motisSource) {
                        Log::error('Motis source not found for source: ' . $source['file']);

                        continue;
                    }

                    $active = array_key_exists($source['spdx'], MotisSourceLicense::SPDX);

                    $motisSource->update(['spdx' => $source['spdx'], 'active' => $active]);
                    Log::debug('Updated source with SPDX license: ' . $source['file'] . ' -> ' . $source['spdx']);
                }
            } catch (Exception $e) {
                Log::error('Failed to create source: ' . $e->getMessage());
            }
        }
    }

    private function getLicenseJson(): array
    {
        Log::debug('Starting to fetch licenses from license repository');
        $response = Http::get(self::LICENSE_URL);
        if (!$response->successful()) {
            Log::error('Failed to download licenses: HTTP ' . $response->status());

            return [];
        }
        try {
            return json_decode($response->body(), true);
        } catch (Exception $e) {
            Log::error('Failed to decode licenses: ' . $e->getMessage());

            return [];
        }
    }
}
