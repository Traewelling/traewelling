<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Requests\admin\LicenseIndexFilterRequest;
use App\Http\Requests\CreateLicenseRequest;
use App\Models\License;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class LicensesController
{
    public function index(LicenseIndexFilterRequest $request)
    {
        $licenses = License::paginate(50);
        if (!empty($request->validated())) {
            $licenses = License::whereNotNull('id');

            if ($request->name) {
                $licenses->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->human_name) {
                $licenses->where('human_name', 'like', '%' . $request->human_name . '%');
            }
            if ($request->automatically_activate_source) {
                $licenses->where('automatically_activate_source', $request->automatically_activate_source);
            }
            $licenses = $licenses->orderBy('automatically_activate_source', 'desc')
                ->orderBy('name')
                ->paginate(50);
        }

        $licenses->appends($request->validated())->links();

        return view(
            'admin.licenses.index',
            [
                'licenses' => $licenses,
                'filter' => $request,
            ]
        );
    }

    public function create(): View|Application|Factory
    {
        return view('admin.licenses.create');
    }

    public function store(CreateLicenseRequest $request): Redirector|RedirectResponse
    {
        License::create($request->validated());

        return redirect(route('licenses.index'))
            ->with('success', 'License created successfully');
    }
}
