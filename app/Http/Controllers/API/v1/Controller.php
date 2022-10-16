<?php

namespace App\Http\Controllers\API\v1;

/**
 * @OA\Info(
 *      version="1.0.0 - alpha",
 *      title="Träwelling API",
 *      description="Träwelling user API description. This is an incomplete documentation with still many errors.",
 *      @OA\Contact(
 *          email="support@traewelling.de"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server (
 *     url="https://traewelling.de/api/v1",
 *     description="Production Server"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="This instance"
 * )
 *
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     securityScheme="token",
 *     name="Authorization"
 * )
 *
 *
 * @OA\Tag(
 *     name="User",
 *     description="Information regarding users"
 * )
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API Endpoints of Dashboard"
 * )
 * @OA\Tag(
 *     name="Status",
 *     description="Endpoints for accessing and manipulating Statusses and their additional data"
 * )
 * @OA\Tag(
 *     name="Events",
 *     description="Events that users can check in to"
 * )
 * @OA\Tag(
 *     name="Likes",
 *     description="Likes regarding a single status"
 * )
 * @OA\Tag(
 *     name="Settings",
 *     description="User/Profile-Settings"
 * )
 */
class Controller
{

}
