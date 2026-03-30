<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Exceptions\RateLimitExceededException;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController
{
    use SendsPasswordResetEmails;

    public function __construct(
        private PrivacyPolicyRepository $privacyPolicyRepository
    ) {}

    public function renderIndex(Request $request): View|RedirectResponse
    {
        $validated = $request->validate(['query' => ['nullable'], 'mailchange_id' => ['nullable']]);
        if (isset($validated['mailchange_id'])) {
            $usersQuery = User::whereHas('mailChanges', function ($query) use ($validated) {
                $query->where('id', 'LIKE', $validated['mailchange_id'] . '%');
            });
        } elseif (!isset($validated['query'])) {
            $usersQuery = User::orderByDesc('last_login');
        } else {
            if (preg_match('/^["\'“”„].*["\'“”„]$/', $validated['query'])) {
                $validated['query'] = substr($validated['query'], 1, -1);
                $usersQuery = User::where('id', $validated['query'])
                    ->orWhere('name', $validated['query'])
                    ->orWhere('username', $validated['query'])
                    ->orWhere('email', $validated['query'])
                    ->orderByDesc('last_login');
            } else {
                $usersQuery = User::where('id', $validated['query'])
                    ->orWhere('name', 'like', '%' . $validated['query'] . '%')
                    ->orWhere('username', 'like', '%' . $validated['query'] . '%')
                    ->orWhere('email', 'like', '%' . $validated['query'] . '%')
                    ->orderByDesc('last_login');
            }
        }

        $users = $usersQuery->simplePaginate(10);

        if ($users->count() === 1) {
            return redirect()->route('admin.users.show', ['id' => $users->first()->id]);
        }

        return view('admin.users.index', [
            'users' => $users,
            'query' => $validated['query'] ?? '',
            'userId' => $validated['userId'] ?? '',
        ]);
    }

    public function renderUser(int $id): View
    {
        $user = User::findOrFail($id);
        $current = $this->privacyPolicyRepository->getPrivacyPolicyValidAt(now());
        $future = $this->privacyPolicyRepository->getPrivacyPolicyValidAt(now()->addYear());
        $acceptedFuture = null;

        if ($current->id !== $future->id) {
            $acceptedFuture = $this->privacyPolicyRepository->getUserPolicyAcceptance($user, $future)->first();
        }

        $acceptedCurrent = $this->privacyPolicyRepository->getUserPolicyAcceptance($user, $current)->first();

        return view('admin.users.show', [
            'user' => $user,
            'privacyPolicyCurrent' => $acceptedCurrent,
            'privacyPolicyFuture' => $acceptedFuture,
            'privacyPolicyFutureExists' => $current->id !== $future->id,
        ]);
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);
        $user = User::findOrFail($validated['id']);
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

        return redirect()->route('admin.users.show', ['id' => $validated['id']]);
    }

    public function updateRoles(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'roles' => ['array'],
        ]);
        $user = User::findOrFail($validated['id']);
        $roles = [];
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

        return redirect()->route('admin.users.show', ['id' => $validated['id']]);
    }
}
