<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Models\RouteSegment;
use Illuminate\View\View;

class RouteSegmentController
{

    public function renderSegment(string $id): View {
        $segment = RouteSegment::findOrFail($id);
        return view('admin.routesegment.show', [
            'segment' => $segment
        ]);
    }
}
