<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Requests\admin\SourceIndexFilterRequest;
use App\Models\MotisSourceLicense;

class MotisSourceController
{
    public function index(SourceIndexFilterRequest $request) {
        $licenses = MotisSourceLicense::paginate(50);
        if (!empty($request->validated())) {
            $licenses = MotisSourceLicense::whereNotNull('id');

            if ($request->country) {
                $licenses->where('country', 'like', $request->country . '%');
            }
            if ($request->name) {
                $licenses->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->human_name) {
                $licenses->where('human_name', 'like', '%' . $request->human_name . '%');
            }
            if ($request->active) {
                $licenses->where('active', $request->active);
            }
            $licenses = $licenses->orderBy('active', 'desc')
                                 ->orderBy('country')
                                 ->orderBy('name')
                                 ->paginate(50);
        }


        $licenses->appends($request->validated())->links();
        return view(
            'admin.sources.index',
            [
                'licenses' => $licenses,
                'filter'   => $request,
            ]
        );
    }
}
