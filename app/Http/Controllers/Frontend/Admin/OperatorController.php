<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Operator::class);

        return view('admin.operators.index', [
            'operators' => Operator::with('identifiers')->orderByDesc('id')->get(), // it's a long list, but... then we don't need to paginate it * duck and cover *
        ]);
    }
}
