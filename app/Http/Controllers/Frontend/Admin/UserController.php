<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Exceptions\RateLimitExceededException;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController
{
    use SendsPasswordResetEmails;

    public function renderIndex(Request $request): View|RedirectResponse {
        $validated = $request->validate(['query' => ['nullable']]);
        if (!isset($validated['query'])) {
            $users = User::orderByDesc('last_login')->simplePaginate(10);
        } else {
            if (preg_match('/^["\'“”„].*["\'“”„]$/', $validated['query'])) {
                $validated['query'] = substr($validated['query'], 1, -1);
                $users              = User::where('id', $validated['query'])
                                          ->orWhere('name', $validated['query'])
                                          ->orWhere('username', $validated['query'])
                                          ->orWhere('email', $validated['query'])
                                          ->orderByDesc('last_login')
                                          ->simplePaginate(10);
            } else {
                $users = User::where('id', $validated['query'])
                             ->orWhere('name', 'like', '%' . $validated['query'] . '%')
                             ->orWhere('username', 'like', '%' . $validated['query'] . '%')
                             ->orWhere('email', 'like', '%' . $validated['query'] . '%')
                             ->orderByDesc('last_login')
                             ->simplePaginate(10);
            }
        }

        if ($users->count() === 1) {
            return redirect()->route('admin.users.user', ['id' => $users->first()->id]);
        }

        return view('admin.users.index', [
            'users'  => $users,
            'query'  => $validated['query'] ?? '',
            'userId' => $validated['userId'] ?? ''
        ]);
    }

    public function renderUser(int $id): View {
        $user = User::findOrFail($id);
        return view('admin.users.show', [
            'user' => $user
        ]);
    }

    public function updateMail(Request $request): RedirectResponse {
        $validated   = $request->validate([
                                              'id'    => ['required', 'integer', 'exists:users,id'],
                                              'email' => ['required', 'email', 'unique:users,email']
                                          ]);
        $user        = User::findOrFail($validated['id']);
        $user->email = $validated['email'];
        $user->save();
        try {
            $user->sendEmailVerificationNotification();
        } catch (RateLimitExceededException) {
            // Ignore
        }
        if ($user->password === null) {
            $this->sendResetLinkEmail($request);
        }
        return redirect()->route('admin.users.user', ['id' => $validated['id']]);
    }

    public function updateRoles(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'id'    => ['required', 'integer', 'exists:users,id'],
                                            'roles' => ['array'],
                                        ]);
        $user      = User::findOrFail($validated['id']);
        $roles     = [];
        foreach (Role::all() as $role) {
            if ($role->name === 'admin') {
                continue;
            }
            if (isset($validated['roles'][$role->name])) {
                $roles[] = $role->name;
            }
        }
        if ($user->hasRole('admin')) {
            $roles[] = 'admin';
        }
        $user->syncRoles($roles);
        return redirect()->route('admin.users.user', ['id' => $validated['id']]);
    }
}
