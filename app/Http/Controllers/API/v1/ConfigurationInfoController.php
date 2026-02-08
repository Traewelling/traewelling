<?php

namespace App\Http\Controllers\API\v1;

use App\Dto\ConfigurationInformation\ConfigurationInformation;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Configuration Information',
    description: 'Endpoints related to application configuration information.'
)]
class ConfigurationInfoController
{
    private \App\Http\Controllers\Backend\ConfigurationInfoController $backendController;

    public function __construct(\App\Http\Controllers\Backend\ConfigurationInfoController $backendController)
    {
        $this->backendController = $backendController;
    }

    #[OA\Get(
        path: '/app/configuration',
        operationId: 'getConfigurationInfo',
        summary: 'Get Application Configuration Information',
        description: 'Retrieves configuration information about the application, including features and supported languages.',
        tags: ['Configuration Information'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful retrieval of configuration information.',
                content: new OA\JsonContent(ref: ConfigurationInformation::class)
            ),
        ]
    )]
    public function getConfigurationInfo(): \App\Dto\ConfigurationInformation\ConfigurationInformation
    {
        return $this->backendController->getConfigurationInfo();
    }
}
