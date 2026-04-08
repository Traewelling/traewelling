<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WebhookCallLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookCallLogFactory extends Factory
{
    protected $model = WebhookCallLog::class;

    public function definition(): array
    {
        return [
            'webhook_id' => null,
            'user_id' => 1,
            'oauth_client_id' => 1,
            'event' => 'checkin_create',
            'url' => $this->faker->url(),
            'attempt' => 1,
            'response_code' => 200,
            'created_at' => now(),
        ];
    }
}
