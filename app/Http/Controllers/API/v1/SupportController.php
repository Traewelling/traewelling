<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\Support\TicketController;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends ResponseController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createTicket(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'subject' => ['required', 'string', 'max:255'],
                                            'message' => ['required', 'string',]
                                        ]);

        try {
            $ticketNumber = TicketController::createTicket(
                user:    auth()->user(),
                subject: $validated['subject'],
                message: $validated['message'],
            );
            return $this->sendv1Response(['ticket' => $ticketNumber], 201);
        } catch (GuzzleException $exception) {
            report($exception);
            return $this->sendv1Error(null, 503);
        }
    }
}
