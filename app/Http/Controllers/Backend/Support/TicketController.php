<?php

namespace App\Http\Controllers\Backend\Support;

use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

abstract class TicketController extends Controller
{
    /**
     * Creates a ticket in the help desk.
     *
     * @param User $user
     * @param      $subject
     * @param      $message
     *
     * @return int Ticket-Number
     * @throws GuzzleException
     * @throws RateLimitExceededException
     */
    public static function createTicket(User $user, $subject, $message): int {
        if ($user->email === null || $user->email_verified_at === null) {
            throw new InvalidArgumentException('E-Mail address is missing.');
        }

        if (RateLimiter::tooManyAttempts('create-ticket:' . $user->id, 1)) {
            throw new RateLimitExceededException();
        }
        RateLimiter::hit('create-ticket:' . $user->id);

        $client = new Client(['base_uri' => config('ticket.host')]);
        $result = $client->post('/api/tickets.json', [
            'json'    => [
                'name'    => $user->username,
                'email'   => $user->email,
                'subject' => $subject,
                'message' => $message,
            ],
            'headers' => [
                'X-API-Key' => config('ticket.api_key'),
            ],
        ]);
        return (int) $result->getBody()->getContents();


    }
}
