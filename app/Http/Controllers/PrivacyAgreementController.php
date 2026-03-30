<?php

namespace App\Http\Controllers;

use App\Exceptions\AlreadyAcceptedException;
use App\Services\PrivacyPolicyService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class PrivacyAgreementController extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyService $privacyPolicyService
    ) {}

    public function intercept(?string $date = null): Renderable
    {
        $agreement = $this->privacyPolicyService->getPrivacyPolicy($date);
        $user = Auth::user();
        $hasUserSigned = false;
        $policyChanged = false;

        if ($user) {
            $acceptances = $this->privacyPolicyService->getUserAcceptance($user);
            $hasUserSigned = $this->privacyPolicyService->hasUserAcceptedPolicy($user, $agreement);
            if (count($acceptances) > 0) {
                $policyChanged = true;
            }
        }

        return view('legal.privacy-interception', ['agreement' => $agreement, 'user' => $user, 'hasUserSigned' => $hasUserSigned, 'policyChanged' => $policyChanged]);
    }

    public function ack(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'valid_at' => 'nullable|date',
        ]);
        try {
            $this->privacyPolicyService->acceptPrivacyPolicy(user: auth()->user(), validAt: $validated['valid_at'] ?? null);
        } catch (AlreadyAcceptedException) {
            return redirect()->route('dashboard');
        }

        if ($request->is('api*')) {
            return response()->json(['message' => 'privacy agreement successfully accepted'], 202);
        }

        return redirect()->route('dashboard');
    }
}
