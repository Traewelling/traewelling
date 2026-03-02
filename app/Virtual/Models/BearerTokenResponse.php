<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'BearerTokenResponse',
    xml: new OA\Xml(name: 'BearerTokenResponse'),
)]
class BearerTokenResponse
{
    #[OA\Property(
        title: 'token',
        description: "Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)",
        type: 'string',
        example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZWU2ZWZiOWUxYTIwN2FmMjZjNjk4NjVkOTA5ODNmNzFjYzYyMzE5ODA3NGU1NjlhNjU1MGRiMTdhMWY5YmNhMmY4ZjNjNTQ4ZGZkZTY5ZmUiLCJpYXQiOjE2NjYxODUzMDYuOTczODU3LCJuYmYiOjE2NjYxODUzMDYuOTczODYsImV4cCI6MTY5NzcyMTMwNi45NDYyNDgsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.tiv8VeL8qw6BRwo5QZZ71Zn3WnFJjtvVciahiUJjzVNfqgofdRF6EoWrTFc_WmrgbVCdfXBjBI02fjbSrsD4.....',
    )]
    private string $token;

    #[OA\Property(
        title: 'slug',
        description: 'end of life for this token. Lifespan is usually one year.',
        type: 'string',
        example: '2023-10-19T15:15:06+02:00',
    )]
    private string $expires_at;
}
