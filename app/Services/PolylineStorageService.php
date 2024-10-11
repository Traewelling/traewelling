<?php

namespace App\Services;

use App\Models\PolyLine;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PolylineStorageService
{
    private const string POLYLINE_STORAGE_PATH = 'polylines';
    private Filesystem $disk;
    private ?string    $content = null;

    public function __construct() {
        $this->disk = Storage::build([
                                         'driver' => 'local', // ToDo: make this configurable in .env
                                         'root'   => storage_path(self::POLYLINE_STORAGE_PATH),
                                     ]);
    }

    public function store(string $content, string $hash = null): string {
        $hash = $hash ?? md5($content);
        $this->disk->put($this->storageName($hash), $content);

        return $content;
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
            $content = $this->store($content, $hash);
            $polyLine->update(['polyline' => '{}']);

            return $content;
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
