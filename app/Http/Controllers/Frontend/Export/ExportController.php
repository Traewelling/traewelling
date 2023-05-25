<?php

namespace App\Http\Controllers\Frontend\Export;

use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $from  = Carbon::parse($validated['from']);
        $until = Carbon::parse($validated['until']);
        if ($from->diffInDays($until) > 365) {
            return back()->with('error', __('export.error.time'));
        }

        try {
            return ExportBackend::generateExport(
                from:     $from,
                until:    $until,
                filetype: $validated['filetype']
            );
        } catch (DataOverflowException) {
            return back()->with('error', __('export.error.amount'));
        }
    }
}
