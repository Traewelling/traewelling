<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function renderSitemap(): Response {
        $sitemap = SitemapGenerator::create(config('app.url'))->getSitemap();
        $this->addProfiles($sitemap);

        return response($sitemap->render(), 200, [
            'Content-type' => 'application/xml'
        ]);
    }

    private function addProfiles(Sitemap $sitemap): void {
        $profiles = DB::table('users')
                      ->join('statuses', 'users.id', '=', 'statuses.user_id')
                      ->where('prevent_index', 0)
                      ->groupBy('users.id')
                      ->select([
                                   'users.username',
                                   DB::raw('MAX(statuses.created_at) AS last_mod')
                               ])
                      ->get();

        foreach ($profiles as $profile) {
            $url = Url::create(route('account.show', ['username' => $profile->username]))
                      ->setPriority(0.5)
                      ->setLastModificationDate(Carbon::parse($profile->last_mod))
                      ->setChangeFrequency('weekly');
            $sitemap->add($url);
        }
    }
}
