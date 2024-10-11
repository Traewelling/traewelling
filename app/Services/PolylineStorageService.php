<?php

namespace App\Services;

use App\Models\PolyLine;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PolylineStorageService
{
    private Filesystem $disk;
    private ?string    $content = null;

    public function __construct() {
        $this->disk = Storage::build([
                                         'driver' => config('trwl.polyline_storage_driver'),
                                         'root'   => storage_path(config('trwl.polyline_storage_path')),
                                     ]);
    }

    private function store(string $content, string $hash = null): bool {
        $hash = $hash ?? md5($content);

        if ($this->disk->exists($this->storageName($hash))) {
            return true;
        }
        return $this->disk->put($this->storageName($hash), $content);
    }

    public function get(string $hash): string {
        if ($this->content !== null) {
            return $this->content;
        }
        if (!$this->disk->exists($this->storageName($hash))) {
            return '';
        }

        return $this->disk->get($this->storageName($hash));
    }

    public function delete(string $hash): void {
        $this->disk->delete($this->storageName($hash));
    }

    public function getOrCreate(PolyLine $polyLine): string {
        $content = $polyLine->getAttribute('polyline');
        $hash    = $polyLine->getAttribute('hash');

        if (!$this->empty($content)) {
            $success = $this->store($content, $hash);

            if ($success && config('trwl.polyline_clear_after_copy')) {
                $polyLine->update(['polyline' => '{}']);
            }
        }

        return $this->get($hash);
    }

    /**
     * Get the storage name for a given hash.
     * This breaks the hash into 4 characters and uses them as subdirectories
     * to avoid having too many files in one directory.
     */
    private function storageName(string $hash): string {
        return substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash;
    }

    private function empty(string $content): bool {
        $content = trim($content);
        return empty($content) || $content === '{}' || $content === '[]';
    }
}
