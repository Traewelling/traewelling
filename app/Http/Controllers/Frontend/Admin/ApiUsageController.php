<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\UserAgent;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiUsageController extends Controller
{
    public function showUsage(Request $request): Renderable {
        $validated = $request->validate([
                                            'start' => ['nullable', 'date', 'before_or_equal:end'],
                                            'end'   => ['nullable', 'date', 'after_or_equal:start'],
                                        ]);
        $start     = isset($validated['start']) ? Carbon::parse($validated['start']) : Carbon::now()->subMonth();
        $end       = isset($validated['end']) ? Carbon::parse($validated['end']) : Carbon::now();

        $mostUsedRoutes = ApiLog::where('created_at', '>', $start->toIso8601String())
                                ->where('created_at', '<', $end->toIso8601String())
                                ->groupBy(['method', 'route'])
                                ->select(['method', 'route', DB::raw('count(*) as count')])
                                ->orderBy('count', 'desc')
                                ->get();

        $mostUsedUserAgents = UserAgent::join('api_logs', 'user_agents.id', '=', 'api_logs.user_agent_id')
                                       ->where('api_logs.created_at', '>', $start->toIso8601String())
                                       ->where('api_logs.created_at', '<', $end->toIso8601String())
                                       ->groupBy(['user_agents.id', 'user_agents.user_agent'])
                                       ->select(['user_agents.id', 'user_agents.user_agent', DB::raw('count(*) as count')])
                                       ->orderBy('count', 'desc')
                                       ->get();

        return view('admin.api.usage', [
            'start'              => $start,
            'end'                => $end,
            'mostUsedRoutes'     => $mostUsedRoutes,
            'mostUsedUserAgents' => $mostUsedUserAgents,
        ]);
    }
}
