<?php

namespace App\Http\Controllers\Backend\Wikidata;


use App\Http\Controllers\Controller;
use App\Models\WikidataEntity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class WikidataFetchController extends Controller
{

    public static function fetchOldestEntity(): void {
        $entityToFetch = WikidataEntity::whereNull('data')
                                       ->orWhere('last_updated_at', '<', now()->subQuarter())
                                       ->orderBy('last_updated_at')
                                       ->limit(1)
                                       ->first();
        if (!$entityToFetch) {
            return;
        }
        self::fetchEntity($entityToFetch);
    }

    public static function fetchEntity(WikidataEntity $entity): void {
        Log::debug('[WikidataFetcher] Fetching entity ' . $entity->id);
        $response = Http::withUserAgent('traewelling/wikidata-fetcher (https://github.com/Traewelling/traewelling)')
                        ->get('https://www.wikidata.org/w/rest.php/wikibase/v0/entities/items/' . $entity->id);
        if ($response->failed()) {
            Log::debug('[WikidataFetcher] Failed to fetch entity ' . $entity->id);
            return;
        }
        $entity->update([
                            'data'            => $response->json(),
                            'last_updated_at' => now(),
                        ]);
    }

}
