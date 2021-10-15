<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Support\Str;

abstract class EventController extends Controller
{

    public static function createEvent(
        string $name,
        string $hashtag,
        string $host,
        TrainStation $trainStation,
        Carbon $begin,
        Carbon $end,
        string $url = null
    ): Event {
        return Event::create([
                                 'name'         => $name,
                                 'slug'         => self::createSlug($name),
                                 'hashtag'      => $hashtag,
                                 'host'         => $host,
                                 'trainstation' => $trainStation->id,
                                 'begin'        => $begin->toIso8601String(),
                                 'end'          => $end->toIso8601String(),
                                 'url'          => $url
                             ]);
    }

    private static function createSlug(string $name): string {
        $slug = Str::slug($name, '_');

        $i = "";
        while (Event::where('slug', '=', $slug . $i)->first()) {
            $i = $i === "" ? 1 : $i + 1;
        }
        if ($i !== "") {
            return $slug . $i;
        }
        return $slug;
    }
}
