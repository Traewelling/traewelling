<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\VersionController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    public function renderChangelog(): View {
        $changelog = cache()->remember('changelog', now()->addHour(), function() {
            //Fetch changelog from GitHub
            $rawRSS = Http::get('https://github.com/Traewelling/traewelling/releases.atom')->body();
            $xml    = simplexml_load_string($rawRSS);
            //the xml object can't be serialized for caching so we convert it to json and back
            $json = json_encode($xml);
            return json_decode($json, true);
        });

        // prevent changelog from showing future versions
        $currentVersion = VersionController::getVersion();
        if (str_starts_with($currentVersion, 'v') && $changelog['entry'][0]['title'] !== $currentVersion) {
            array_shift($changelog['entry']);
        }
        foreach ($changelog['entry'] as $key => $entry) {
            //sanitize content and remove all html tags but allowed
            $changelog['entry'][$key]['content'] = strip_tags($entry['content'], '<p><ul><li><h1><h2><h3><h4><h5><h6><a><strong><em><blockquote><code><pre><img><br><hr><table><thead><tbody><tr><th><td>');

            //change h2 to h4
            $changelog['entry'][$key]['content'] = str_replace(['<h2>', '</h2>'], ['<h4>', '</h4>'], $changelog['entry'][$key]['content']);

            //add target="_blank" to all links
            $changelog['entry'][$key]['content'] = str_replace('<a ', '<a target="_blank" ', $changelog['entry'][$key]['content']);

            //parse updated date
            $changelog['entry'][$key]['updated'] = Date::parse($entry['updated']);
        }

        return view('changelog', [
            'changelog' => $changelog,
        ]);
    }
}
