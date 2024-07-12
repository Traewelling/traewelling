<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class SitemapController extends Controller
{
    public function renderSitemap(Request $request): Response {
        $sitemap = SitemapGenerator::create(config('app.url'))
                                   ->shouldCrawl(function() {
                                       return false;
                                   })
                                   ->getSitemap();

        if ($request->has('static')) {
            $this->addStatic($sitemap);
        }
        if ($request->has('profiles')) {
            $this->addProfiles($sitemap);
        }
        if ($request->has('events')) {
            $this->addEvents($sitemap);
        }

        return response($sitemap->render(), 200, [
            'Content-type' => 'application/xml'
        ]);
    }

    private function addStatic(Sitemap $sitemap): void {
        $sitemap->add(Url::create(route('leaderboard'))->setPriority(0.7));
        $sitemap->add(Url::create(route('statuses.active'))->setPriority(0.6));

        $dates = DB::table('statuses')
                   ->whereNotNull('created_at')
                   ->groupBy([
                                 DB::raw('YEAR(created_at)'),
                                 DB::raw('MONTH(created_at)'),
                             ])
                   ->select([
                                DB::raw('YEAR(created_at) AS year'),
                                DB::raw('MONTH(created_at) AS month'),
                            ])
                   ->get();

        foreach ($dates as $date) {
            $sitemap->add(Url::create(route('leaderboard.month', ['date' => $date->year . '-' . $date->month]))
                             ->setPriority(0.6));
        }
    }

    private function addEvents(Sitemap $sitemap): void {
        $sitemap->add(Url::create(route('events'))->setPriority(0.7));

        $eventIdsWithCheckins = Status::whereNotNull('event_id')->select('event_id');
        $events               = Event::whereIn('id', $eventIdsWithCheckins)
                                     ->orWhere('end', '>=', DB::raw('CURRENT_TIMESTAMP'))
                                     ->get();

        foreach ($events as $event) {
            $sitemap->add(Url::create(route('event', ['slug' => $event->slug]))
                             ->setPriority(0.6));
        }
    }

    private function addProfiles(Sitemap $sitemap): void {
        $profiles = DB::table('users')
                      ->join('statuses', 'users.id', '=', 'statuses.user_id')
                      ->where('prevent_index', 0)
                      ->groupBy(['users.id', 'users.username'])
                      ->select([
                                   'users.username',
                                   DB::raw('MAX(statuses.created_at) AS last_mod')
                               ])
                      ->get();

        foreach ($profiles as $profile) {
            $url = Url::create(route('profile', ['username' => $profile->username]))
                      ->setPriority(0.5)
                      ->setLastModificationDate(Carbon::parse($profile->last_mod))
                      ->setChangeFrequency('weekly');
            $sitemap->add($url);
        }
    }
}
