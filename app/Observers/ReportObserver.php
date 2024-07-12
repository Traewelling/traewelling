<?php

namespace App\Observers;

use App\Models\Report;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class ReportObserver
{
    public function created(Report $report): void {
        if (App::runningUnitTests() || config('app.admin.notification.url') === null) {
            return;
        }
        Http::post(config('app.admin.notification.url'), [
            'chat_id'    => config('app.admin.notification.chat_id'),
            'text'       => "<b>🚨 New Report for " . $report->subject_type . "</b>" . PHP_EOL
                            . "Reason: " . $report->reason?->value . PHP_EOL
                            . "Description: " . ($report->description ?? 'None') . PHP_EOL
                            . "View Report: " . config('app.url') . "/admin/reports/" . $report->id . PHP_EOL
            ,
            'parse_mode' => 'HTML',
        ]);
    }
}
