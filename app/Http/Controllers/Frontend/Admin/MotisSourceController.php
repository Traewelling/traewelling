<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Requests\admin\MassAssignSourceRequest;
use App\Http\Requests\admin\SourceIndexFilterRequest;
use App\Models\License;
use App\Models\MotisSourceLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class MotisSourceController
{
    public function massAssign(MassAssignSourceRequest $request): Redirector|RedirectResponse
    {
        $license = License::where('id', $request->license_id)->firstOrFail();

        $update = [
            'license_id' => $license->id,
            'force_active' => $license->automatically_activate_source,
        ];

        if ($license->automatically_activate_source) {
            $update['active'] = 1;
        }

        MotisSourceLicense::whereIn('id', $request->source_ids)
            ->update($update);

        return redirect(route('admin.sources'))
            ->with('success', 'Sources assigned successfully');
    }

    public function index(SourceIndexFilterRequest $request)
    {
        $sources = MotisSourceLicense::with('manualLicense')->paginate(50);
        if (!empty($request->validated())) {
            $sources = MotisSourceLicense::with('manualLicense')->whereNotNull('id');

            if ($request->country) {
                $sources->where('country', 'like', $request->country . '%');
            }
            if ($request->name) {
                $sources->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->human_name) {
                $sources->where('human_name', 'like', '%' . $request->human_name . '%');
            }
            if ($request->active) {
                $sources->where('active', $request->active);
            }
            $sources = $sources->orderBy('active', 'desc')
                ->orderBy('country')
                ->orderBy('name')
                ->paginate(50);
        }

        $sources->appends($request->validated())->links();

        return view(
            'admin.sources.index',
            [
                'sources' => $sources,
                'filter' => $request,
                'licenses' => License::whereNotNull('id')
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }
}
