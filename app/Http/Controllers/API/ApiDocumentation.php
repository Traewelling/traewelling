<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use OpenApi\Attributes as OA;

/**
 * Document level metadata of the public API: info block, servers and tags.
 *
 * This deliberately does not live in a versioned namespace. The API is versioned per
 * endpoint, not as a whole: the server url ends at `/api` and every operation carries its
 * own version as the first path segment (`/v1/status/{id}`). A single endpoint can
 * therefore be published as `/v2/...` without moving the rest of the API along with it.
 *
 * There is nothing to implement here. The class exists so that swagger-php has a stable
 * place to read these annotations from.
 */
#[OA\Info(
    version: '1.0.0 - alpha',
    description: 'Träwelling user API description. Breaking changes will be announced on GitHub: https://github.com/Traewelling/traewelling/blob/develop/API_CHANGELOG.md' . "\n\n" .
                 'Endpoints are versioned individually. The version is the first segment of every path, so `/v1/status/{id}` and a future `/v2/status/{id}` are two independent endpoints that can be offered side by side.',
    title: 'Träwelling API',
    contact: new OA\Contact(email: 'support@traewelling.de'),
    license: new OA\License(name: 'Apache 2.0', url: 'https://www.apache.org/licenses/LICENSE-2.0.html'),
)]
#[OA\Server(url: 'https://traewelling.de/api', description: 'Production Server')]
#[OA\Server(url: L5_SWAGGER_CONST_ENDPOINT, description: 'This instance')]
#[OA\Tag(name: 'Auth', description: 'Logging in, creating Accounts, etc.')]
#[OA\Tag(name: 'Checkin', description: 'Checkin related endpoints. Regular process is departures -> trip -> checkin')]
#[OA\Tag(name: 'Dashboard', description: 'API Endpoints of Dashboard')]
#[OA\Tag(name: 'Events', description: 'Events that users can check in to')]
#[OA\Tag(name: 'ICS Tokens', description: 'Manage ICS Tokens for calendar export')]
#[OA\Tag(name: 'Leaderboard', description: 'Leaderboard related endpoints')]
#[OA\Tag(name: 'Likes', description: 'Likes regarding a single status')]
#[OA\Tag(name: 'Notifications', description: 'Get notifications and mark them as read or unread')]
#[OA\Tag(name: 'Polyline', description: 'Manage route polylines and segments')]
#[OA\Tag(name: 'Report', description: 'Report a Status, Event or User to the admins')]
#[OA\Tag(name: 'Settings', description: 'User/Profile-Settings')]
#[OA\Tag(name: 'Statistics', description: 'Statistics related endpoints')]
#[OA\Tag(name: 'Status', description: 'Endpoints for accessing and manipulating Statusses and their additional data')]
#[OA\Tag(name: 'User', description: 'Information regarding users')]
#[OA\Tag(name: 'User/Follow', description: 'Follow and unfollow users, manage your followers')]
#[OA\Tag(name: 'User/Hide and Block', description: 'Mute and block users')]
#[OA\Tag(name: 'Webhooks', description: 'Manage Webhooks for third party applications')]
final class ApiDocumentation {}
