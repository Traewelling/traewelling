<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

abstract class TelegramController extends Controller
{
    public static function sendAdminMessage(string $message) {
        $client   = new Client(['base_uri' => 'https://api.telegram.org']);
        $response = $client->get(strtr('/bot:token/sendMessage', [':token' => config('app.telegram.token')]), [
            'query' => [
                'chat_id'    => config('app.telegram.admin_id'),
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]
        ]);
    }
}
