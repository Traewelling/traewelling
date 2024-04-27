<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\Report\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\View\View;

class ReportController extends Controller
{

    public function renderReports(): View {
        $this->authorize('viewAny', Report::class);
        return view('admin.reports.list', [
            'reports' => Report::paginate(15),
        ]);
    }

    public function showReport(int $id): View {
        $report = Report::findOrFail($id);
        $this->authorize('view', $report);
        return view('admin.reports.show', [
            'report' => $report,
        ]);
    }
}
