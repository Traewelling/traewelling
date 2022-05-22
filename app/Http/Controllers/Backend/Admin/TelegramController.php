<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

abstract class TelegramController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public static function sendAdminMessage(string $message): void {
        if (config('app.telegram.token') === null) {
            Log::error('Telegram token is not set');
            return;
        }
        $client = new Client(['base_uri' => 'https://api.telegram.org']);
        $client->get(strtr('/bot:token/sendMessage', [':token' => config('app.telegram.token')]), [
            'query' => [
                'chat_id'    => config('app.telegram.admin_id'),
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]
        ]);
    }
}
