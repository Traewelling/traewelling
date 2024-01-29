<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{

    public function renderList(Request $request): View {
        $this->authorize('viewAny', Station::class);
        $stations = Station::orderByDesc('created_at');
        if ($request->has('query')) {
            $stations->where('name', 'LIKE', '%' . strip_tags($request->get('query')) . '%')
                     ->orWhere('ibnr', 'LIKE', '%' . strip_tags($request->get('query')) . '%')
                     ->orWhere('rilIdentifier', 'LIKE', '%' . strip_tags($request->get('query')) . '%');
        }
        return view('admin.stations.list', [
            'stations' => $stations->paginate(20),
        ]);
    }
}
