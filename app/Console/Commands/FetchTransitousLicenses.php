<?php

namespace App\Console\Commands;

use App\Models\MotisSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FetchTransitousLicenses extends Command
{
    protected $signature   = 'app:fetch-transitous-licenses';
    protected $description = 'Command description';

    public function handle() {
        // fetch the git repository https://github.com/public-transport/transitous.git and import all licenses from the folder feeds
        // into the database

        // 1. Clone the repository
        // 2. Find all files in the folder feeds
        // 3. Read the files and extract the license information
        // 4. Save the license information in the database
        // 5. Delete the repository
        // 6. Return the number of licenses imported

        $path = storage_path('transitous');
        $repo = 'https://github.com/public-transport/transitous/archive/refs/heads/main.zip';
        $this->info('Cloning repository...');

        $response = Http::head($repo);
        if ($response->status() !== 200) {
            $this->error('Failed to clone repository');
            return self::FAILURE;
        }

        // Download the zip file
        $this->info('Downloading repository...');
        $response = Http::get($repo);
        if ($response->status() !== 200) {
            $this->error('Failed to download repository');
            return self::FAILURE;
        }
        // Save the zip file
        Storage::disk('local')->put('transitous.zip', $response->body());
        $zipFile = Storage::disk('local')->path('transitous.zip');
        $zipper  = new ZipArchive();
        if ($zipper->open($zipFile) === true) {
            $zipper->extractTo($path);
            $zipper->close();
        } else {
            $this->error('Failed to unzip repository');
            return self::FAILURE;
        }

        $this->info('Reading licenses...');

        $licenses = [];
        $files    = glob($path . '/transitous-main/feeds/*');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = $content ? json_decode($content, true) : [];
            $country = basename($file, '.json');
            if (empty($content)) {
                $this->error('Failed to read file: ' . $file);
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
                ];
                $this->info(
                    'Found license: ' . $tmp['name'] . ' (' . $tmp['spdx'] . ')'
                );
                $licenses[] = $tmp;

                MotisSource::updateOrCreate(
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
                    ]
                );
            }
        }
    }
}
