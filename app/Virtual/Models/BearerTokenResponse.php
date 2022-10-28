<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     title="BearerTokenResponse",
 *     @OA\Xml(
 *         name="BearerTokenResponse"
 *     )
 * )
 */
class BearerTokenResponse
{
    /**
     * @OA\Property(
     *     title="token",
     *     description="Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)",
     *     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZWU2ZWZiOWUxYTIwN2FmMjZjNjk4NjVkOTA5ODNmNzFjYzYyMzE5ODA3NGU1NjlhNjU1MGRiMTdhMWY5YmNhMmY4ZjNjNTQ4ZGZkZTY5ZmUiLCJpYXQiOjE2NjYxODUzMDYuOTczODU3LCJuYmYiOjE2NjYxODUzMDYuOTczODYsImV4cCI6MTY5NzcyMTMwNi45NDYyNDgsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.tiv8VeL8qw6BRwo5QZZ71Zn3WnFJjtvVciahiUJjzVNfqgofdRF6EoWrTFc_WmrgbVCdfXBjBI02fjbSrsD4.....",
     * )
     *
     * @var string
     */
    private $token;

    /**
     * @OA\Property (
     *     title="slug",
     *     description="end of life for this token. Lifespan is usually one year.",
     *     example="2023-10-19T15:15:06+02:00"
     * )
     *
     * @var string
     */
    private $expires_at;

}
