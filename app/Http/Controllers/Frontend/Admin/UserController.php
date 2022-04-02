<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;


class UserController
{

    public function renderIndex(Request $request): View|RedirectResponse {
        $validated = $request->validate(['query' => ['nullable'], 'userId' => ['nullable', 'integer']]);

        if (isset($validated['userId'])) {
            $users = User::where('id', $validated['userId'])->simplePaginate(10);
        } elseif (isset($validated['query'])) {
            $users = User::where('name', 'like', '%' . $validated['query'] . '%')
                         ->orWhere('username', 'like', '%' . $validated['query'] . '%')
                         ->simplePaginate(10);
        } else {
            $users = User::simplePaginate(10);
        }

        return view('admin.users.index', [
            'users'  => $users,
            'query'  => $validated['query'] ?? '',
            'userId' => $validated['userId'] ?? ''
        ]);
    }


}
