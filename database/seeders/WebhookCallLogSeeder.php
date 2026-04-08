<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enum\WebhookEvent;
use App\Models\OAuthClient;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCallLog;
use Illuminate\Database\Seeder;

class WebhookCallLogSeeder extends Seeder
{
    public function run(): void
    {
        $gertrud = User::where('username', 'Gertrud123')->first();
        if ($gertrud === null) {
            return;
        }

        $client = OAuthClient::where('user_id', $gertrud->id)
            ->where('personal_access_client', false)
            ->where('password_client', false)
            ->first();

        if ($client === null) {
            return;
        }

        // Ensure a webhook exists for this client
        $webhook = Webhook::firstOrCreate(
            ['oauth_client_id' => $client->id, 'user_id' => $gertrud->id],
            ['url' => 'https://example.com/webhook', 'secret' => bin2hex(random_bytes(16))]
        );

        $events = [
            WebhookEvent::CHECKIN_CREATE->value,
            WebhookEvent::CHECKIN_UPDATE->value,
            WebhookEvent::CHECKIN_DELETE->value,
            WebhookEvent::NOTIFICATION->value,
        ];

        // Simulate realistic call patterns over the last 7 days
        $responseCodes = [
            200, 200, 200, 200, 200, 200, 200, 200, // mostly success
            200, 200, 200, 200, 200, 200, 200, 200,
            404, 500, 503, null,                     // occasional failures
        ];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $callsThisDay = rand(5, 20);
            for ($i = 0; $i < $callsThisDay; $i++) {
                $responseCode = $responseCodes[array_rand($responseCodes)];
                $attempt = $responseCode === null || $responseCode >= 500 ? rand(1, 3) : 1;

                WebhookCallLog::create([
                    'webhook_id' => $webhook->id,
                    'user_id' => $gertrud->id,
                    'oauth_client_id' => $client->id,
                    'event' => $events[array_rand($events)],
                    'url' => $webhook->url,
                    'attempt' => $attempt,
                    'response_code' => $responseCode,
                    'created_at' => now()->subDays($daysAgo)->subMinutes(rand(0, 1380)),
                ]);
            }
        }
    }
}
