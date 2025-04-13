<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Requests\admin\LicenseIndexFilterRequest;
use App\Models\License;

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
}
