<?php

namespace App\Http\Controllers\Frontend\Export;

use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExportController extends Controller
{
    public function renderForm(): View {
        return view('export')->with([
                                        'begin_of_month' => Carbon::now()->firstOfMonth()->format('Y-m-d'),
                                        'end_of_month'   => Carbon::now()->lastOfMonth()->format('Y-m-d')
                                    ]);
    }

    public function renderExport(Request $request) {
        $validated = $request->validate([
                                            'from'     => ['required', 'date', 'before_or_equal:until'],
                                            'until'    => ['required', 'date', 'after_or_equal:from'],
                                            'filetype' => ['required', Rule::in(['json', 'csv', 'pdf'])],
                                        ]);

        return ExportBackend::generateExport(
            from:     Carbon::parse($validated['from']),
            until:    Carbon::parse($validated['until']),
            filetype: $validated['filetype']
        );
    }
}
