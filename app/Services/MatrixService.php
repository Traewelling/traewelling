<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\MatrixException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

readonly class MatrixService
{
    public function __construct(
        private string $homeserver,
        private string $token,
        private string $roomId,
    ) {}

    public static function isAdminActive(): bool
    {
        return filled(config('services.matrix.admin.homeserver'))
            && filled(config('services.matrix.admin.token'));
    }

    public static function adminForEvents(): ?self
    {
        if (!self::isAdminActive()) {
            return null;
        }
        $roomId = config('services.matrix.admin.events_room_id');
        if (!filled($roomId)) {
            return null;
        }

        return new self(
            homeserver: config('services.matrix.admin.homeserver'),
            token: config('services.matrix.admin.token'),
            roomId: $roomId,
        );
    }

    public static function adminForReports(): ?self
    {
        if (!self::isAdminActive()) {
            return null;
        }
        $roomId = config('services.matrix.admin.reports_room_id');
        if (!filled($roomId)) {
            return null;
        }

        return new self(
            homeserver: config('services.matrix.admin.homeserver'),
            token: config('services.matrix.admin.token'),
            roomId: $roomId,
        );
    }

    /**
     * Send an HTML-formatted message to the configured room.
     *
     * @return string Matrix event ID
     *
     * @throws MatrixException
     */
    public function sendMessage(string $htmlText): string
    {
        $txnId = Str::uuid()->toString();
        $encodedRoom = rawurlencode($this->roomId);
        $baseUrl = $this->normalizeHomeserver();
        $url = "{$baseUrl}/_matrix/client/v3/rooms/{$encodedRoom}/send/m.room.message/{$txnId}";

        $formatted = str_replace("\n", '<br>', $htmlText);

        try {
            $response = Http::withToken($this->token)->put($url, [
                'msgtype' => 'm.text',
                'body' => strip_tags($htmlText),
                'format' => 'org.matrix.custom.html',
                'formatted_body' => $formatted,
            ]);
        } catch (ConnectionException $e) {
            throw new MatrixException('Matrix connection error: ' . $e->getMessage(), 0, $e);
        }

        if (!$response->ok()) {
            throw new MatrixException('Matrix API error: ' . $response->body());
        }

        return $response->json('event_id');
    }

    /**
     * Redact (delete) a previously sent message.
     *
     * @throws MatrixException
     */
    public function redactMessage(string $eventId, string $reason = ''): bool
    {
        $txnId = Str::uuid()->toString();
        $encodedRoom = rawurlencode($this->roomId);
        $encodedEvent = rawurlencode($eventId);
        $baseUrl = $this->normalizeHomeserver();
        $url = "{$baseUrl}/_matrix/client/v3/rooms/{$encodedRoom}/redact/{$encodedEvent}/{$txnId}";

        try {
            $response = Http::withToken($this->token)->put($url, ['reason' => $reason]);
        } catch (ConnectionException $e) {
            throw new MatrixException('Matrix connection error: ' . $e->getMessage(), 0, $e);
        }

        if (!$response->ok()) {
            throw new MatrixException('Matrix API error: ' . $response->body());
        }

        return true;
    }

    private function normalizeHomeserver(): string
    {
        $url = rtrim($this->homeserver, '/');

        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            Log::warning('MatrixService: homeserver URL has no scheme, assuming https://.', ['homeserver' => $url]);
            $url = 'https://' . $url;
        }

        return $url;
    }
}
