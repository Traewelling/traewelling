<?php

namespace App\Virtual\Models\Response;

/**
 * @OA\Schema(
 *      title="CheckinForbiddenWithUsersResponse",
 *      @OA\Property (
 *          property="message",
 *          example="You are not allowed to check in the following users: 1"
 *      ),
 *      @OA\Property (
 *          property="meta",
 *          type="object",
 *          @OA\Property (
 *              property="invalidUsers",
 *              type="array",
 *              @OA\Items(
 *                  type="integer",
 *                  example="1"
 *              )
 *          )
 *     )
 * )
 */
class CheckinForbiddenWithUsersResponse
{
}
