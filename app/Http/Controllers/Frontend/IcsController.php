<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\IcsExportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IcsController extends Controller
{
    public function renderIcs(Request $request): ?Response
    {
        $validated = $request->validate([
            'user_id' => ['required', 'numeric'],
            'token' => ['required', 'string'],
            'limit' => ['nullable', 'numeric', 'gte:1', 'lte:10000'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'emojis' => ['nullable', 'boolean'],
        ]);

        $user = User::where('id', $validated['user_id'])->firstOrFail();

        try {
            $service = new IcsExportService($user, $validated['emojis'] ?? true);
            $calendar = $service->generateIcsCalendar(
                token: $validated['token'],
                limit: $validated['limit'] ?? 1000,
                from: isset($validated['from']) ? Carbon::parse($validated['from']) : null,
                until: isset($validated['until']) ? Carbon::parse($validated['until']) : null,
            );

            return response($calendar->get())
                ->header('Content-Type', 'text/calendar')
                ->header('charset', 'utf-8');
        } catch (ModelNotFoundException) {
            abort(403);
        }
    }
}
