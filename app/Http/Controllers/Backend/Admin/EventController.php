<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Str;

abstract class EventController extends Controller
{

    public static function createSlugFromName(string $name): string {
        $slug = Str::slug($name, '_');

        $i = "";
        while (Event::where('slug', '=', $slug . $i)->first()) {
            $i = empty($i) ? 1 : $i + 1;
        }
        if (!empty($i)) {
            return $slug . $i;
        }
        return $slug;
    }
}
