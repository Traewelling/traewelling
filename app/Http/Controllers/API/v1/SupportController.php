<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Backend\Support\TicketController;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
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
            return $this->sendResponse(['ticket' => $ticketNumber], 201);
        } catch (GuzzleException $exception) {
            report($exception);
            return $this->sendError(null, 503);
        } catch (RateLimitExceededException) {
            return $this->sendError(__('support.rate_limit_exceeded'), 429);
        }
    }
}
