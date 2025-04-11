<?php

namespace App\Console\Commands;

use App\Models\MotisSourceLicense;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FetchTransitousLicenses extends Command
{
    private const string REPO = 'https://github.com/public-transport/transitous/archive/refs/heads/main.zip';
    private const string PATH = 'tmp/transitous';

    protected $signature   = 'trwl:fetch-transitous-licenses';
    protected $description = 'Fetch License data from transitous repository';

    public function handle(): int {
        // fetch the git repository https://github.com/public-transport/transitous.git
        // and import all licenses from the folder feeds into the database
        $return = self::SUCCESS;
        if ($this->cloneRepo() !== self::SUCCESS || $this->parseLicenses() !== self::SUCCESS) {
            $this->error('Failed to parse licenses');
            $return = self::FAILURE;
        }

        // Delete the repository
        if ($this->deleteData() !== self::SUCCESS) {
            $this->error('Failed to delete repository');
            $return = self::FAILURE;
        }

        $this->info('Done');
        return $return;
    }

    private function cloneRepo(): int {
        $this->info('Cloning repository...');

        // Download the zip file
        $this->info('Downloading repository...');
        $response = Http::get(self::REPO);
        if (empty($response) || $response->status() !== 200) {
            $this->error('Failed to download repository');
            return self::FAILURE;
        }
        // Save the zip file
        Storage::disk('local')->put(self::PATH . '/transitous.zip', $response->body());
        $zipFile = $this->getStoragePath('transitous.zip');
        $zipper  = new ZipArchive();
        if ($zipper->open($zipFile) === true) {
            $zipper->extractTo($this->getStoragePath());
            $zipper->close();
        } else {
            $this->error('Failed to unzip repository');
            return self::FAILURE;
        }

        // Delete the zip file
        Storage::disk('local')->delete(self::PATH . '/transitous.zip');

        $this->info('Repository cloned successfully');
        return self::SUCCESS;
    }

    private function getStoragePath(?string $path = null): string {
        $storage = Storage::disk('local')->path(self::PATH);

        return $path ? $storage . '/' . $path : $storage;
    }

    private function parseLicenses(): int {
        $this->info('Reading licenses...');
        $files = glob($this->getStoragePath('transitous-main/feeds/*'));

        $errors = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = $content ? json_decode($content, true) : [];
            $country = basename($file, '.json');
            if (empty($content)) {
                $this->error('Failed to read file: ' . $file);
                $errors++;
                continue;
            }

            $this->info('Reading licenses for country: ' . $country);
            foreach ($content['sources'] as $license) {
                $tmp = [
                    'name'        => $license['name'],
                    'url'         => $license['url'] ?? '',
                    'license_url' => $license['license']['url'] ?? '',
                    'version'     => $license['version'] ?? '',
                    'type'        => $license['type'] ?? '',
                    'spdx'        => $license['license']['spdx-identifier'] ?? '',
                    'active'      => array_key_exists($license['license']['spdx-identifier'] ?? '', MotisSourceLicense::SPDX),
                ];
                $this->info(
                    sprintf('[%s] Found license: %s (%s) %s', $country, $tmp['name'], $tmp['spdx'], $tmp['active'] ? 'active' : 'inactive')
                );

                MotisSourceLicense::updateOrCreate(
                    [
                        'provider' => 'transitous',
                        'country'  => $country,
                        'name'     => $tmp['name'],
                    ],
                    [
                        'license_url' => $tmp['license_url'],
                        'source_url'  => $tmp['url'],
                        'spdx'        => $tmp['spdx'],
                        'license'     => $tmp['type'],
                        'active'      => $tmp['active'],
                    ]
                );
            }
        }

        if ($errors == count($files)) {
            $this->error('Failed to read some files');
            return self::FAILURE;
        } elseif ($errors > 0) {
            $this->info('Some licenses read successfully');
        }


        return self::SUCCESS;
    }

    private function deleteData(): int {
        $this->info('Deleting repository...');
        if (Storage::disk('local')->deleteDirectory(self::PATH)) {
            $this->info('Repository deleted successfully');
            return self::SUCCESS;
        }

        $this->error('Failed to delete repository');
        return self::FAILURE;
    }
}
