<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\AdminNotificationResult;
use App\Exceptions\MatrixException;
use App\Exceptions\TelegramException;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    public static function sendEventNotification(string $htmlMessage): AdminNotificationResult
    {
        return self::send($htmlMessage, 'events');
    }

    public static function sendReportNotification(string $htmlMessage): AdminNotificationResult
    {
        return self::send($htmlMessage, 'reports');
    }

    public static function deleteEventNotification(?int $telegramId, ?string $matrixEventId): void
    {
        self::delete($telegramId, $matrixEventId, 'events');
    }

    public static function deleteReportNotification(?int $telegramId, ?string $matrixEventId): void
    {
        self::delete($telegramId, $matrixEventId, 'reports');
    }

    private static function send(string $htmlMessage, string $type): AdminNotificationResult
    {
        $telegramId = null;
        $matrixId = null;

        $telegramService = match ($type) {
            'events' => TelegramService::adminForEvents(),
            'reports' => TelegramService::adminForReports(),
        };

        if ($telegramService === null) {
            Log::debug("AdminNotification [{$type}]: Telegram not configured, skipping.");
        } else {
            try {
                $telegramId = $telegramService->sendMessage($htmlMessage);
                Log::debug("AdminNotification [{$type}]: Telegram message sent.", ['message_id' => $telegramId]);
            } catch (TelegramException $e) {
                Log::warning("AdminNotification [{$type}]: Telegram send failed.", ['error' => $e->getMessage()]);
                report($e);
            }
        }

        $matrixService = match ($type) {
            'events' => MatrixService::adminForEvents(),
            'reports' => MatrixService::adminForReports(),
        };

        if ($matrixService === null) {
            Log::debug("AdminNotification [{$type}]: Matrix not configured, skipping.");
        } else {
            try {
                $matrixId = $matrixService->sendMessage($htmlMessage);
                Log::debug("AdminNotification [{$type}]: Matrix message sent.", ['event_id' => $matrixId]);
            } catch (MatrixException $e) {
                Log::warning("AdminNotification [{$type}]: Matrix send failed.", ['error' => $e->getMessage()]);
                report($e);
            }
        }

        return new AdminNotificationResult(telegramId: $telegramId, matrixId: $matrixId);
    }

    private static function delete(?int $telegramId, ?string $matrixEventId, string $type): void
    {
        if ($telegramId !== null) {
            $telegramService = match ($type) {
                'events' => TelegramService::adminForEvents(),
                'reports' => TelegramService::adminForReports(),
            };

            if ($telegramService !== null) {
                $telegramService->deleteMessage($telegramId);
            }
        }

        if ($matrixEventId !== null) {
            $matrixService = match ($type) {
                'events' => MatrixService::adminForEvents(),
                'reports' => MatrixService::adminForReports(),
            };

            if ($matrixService !== null) {
                try {
                    $matrixService->redactMessage($matrixEventId);
                } catch (MatrixException $e) {
                    report($e);
                }
            }
        }
    }
}
