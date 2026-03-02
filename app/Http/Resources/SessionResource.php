<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\Helper\PrivacyHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SessionResource',
    required: ['id', 'ip', 'userAgent', 'platform', 'deviceType', 'lastActivity'],
    properties: [
        new OA\Property(property: 'id', description: 'The session ID', type: 'string', example: 'abc123'),
        new OA\Property(property: 'ip', description: 'The masked IP address of the session', type: 'string', example: '192.168.***.***'),
        new OA\Property(property: 'userAgent', description: 'The user agent string of the session', type: 'string', example: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
        new OA\Property(property: 'platform', description: 'The platform of the session', type: 'string', example: 'Windows'),
        new OA\Property(property: 'deviceType', description: 'The type representing the device used in the session', type: 'string', example: 'mobile'),
        new OA\Property(property: 'lastActivity', description: 'The timestamp of the last activity in ISO 8601 format', type: 'string', format: 'date-time', example: '2024-06-01T12:34:56Z'),
    ]
)]
class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ip' => PrivacyHelper::maskIpAddress($this->ip_address),
            'userAgent' => $this->user_agent,
            'platform' => $this->platform,
            'deviceType' => $this->getDeviceType(),
            'lastActivity' => Carbon::createFromTimestamp($this->last_activity)->toIso8601String(),
        ];
    }

    private function getDeviceType(): string
    {
        $icon = $this->device_icon;

        if (str_contains($icon, 'mobile')) {
            return 'mobile';
        } elseif (str_contains($icon, 'tablet')) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
}
