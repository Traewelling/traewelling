<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Dto\Coordinate;
use App\Exceptions\Wikidata\FetchException;
use App\Http\Controllers\Controller;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Objects\LineSegment;
use App\Services\StationService;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OperatorController extends Controller
{

    public function renderList(Request $request): View {
        $this->authorize('viewAny', HafasOperator::class);
        return view('admin.operators.list', [
            'operators' => HafasOperator::all(), // it's a long list, but... then we don't need to paginate it * duck and cover *
        ]);
    }
}
