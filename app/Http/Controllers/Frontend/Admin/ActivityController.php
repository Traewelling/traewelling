<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{

    public function render(Request $request): View {
        $this->authorize('view activity');

        $validated = $request->validate([
                                            'subject_type' => ['nullable', 'string'],
                                            'subject_id'   => ['nullable', 'integer'],
                                        ]);

        $activities = Activity::orderByDesc('created_at');
        $activities->where('subject_id', '<>', '1000001');
        $activities->where('created_at', '>', now()->subMonths(3)->toDateString());

        if (isset($validated['subject_type'], $validated['subject_id'])) {
            $activities->where('subject_type', $validated['subject_type']);
            $activities->where('subject_id', $validated['subject_id']);
        }

        return view('admin.activity.page', [
            'activities' => $activities->paginate(15),
        ]);
    }
}
