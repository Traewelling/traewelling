<?php

namespace App\Http\Controllers\Frontend\Support;

use App\Http\Controllers\Backend\Support\TicketController;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{

    public static function renderSupportPage(): View {
        return view('support.form', [
            'emailAvailable' => isset(auth()->user()->email, auth()->user()->email_verified_at)
        ]);
    }

    public function submit(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'subject' => ['required', 'string', 'max:255'],
                                            'message' => ['required', 'string',]
                                        ]);

        try {
            $ticketNumber = TicketController::createTicket(auth()->user(), $validated['subject'], $validated['message']);
            return back()->with('success', __('support.success', ['ticketNumber' => $ticketNumber]));
        } catch (GuzzleException $exception) {
            report($exception);
            return back()->with('error', __('messages.exception.general'));
        }
    }
}
