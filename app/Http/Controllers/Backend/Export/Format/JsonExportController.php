<?php

namespace App\Http\Controllers\Backend\Export\Format;

use App\Http\Controllers\Backend\Export\ExportController;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusExportResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JetBrains\PhpStorm\ArrayShape;

abstract class JsonExportController extends Controller
{

    #[ArrayShape([
        'meta' => "array",
        'data' => AnonymousResourceCollection::class
    ])]
    public static function generateExport(User $user, Carbon $timestampFrom, Carbon $timestampTo): array {
        $preparedData = ExportController::getExportableStatuses($user, $timestampFrom, $timestampTo);
        return [
            'meta' => [
                'user'       => new UserResource($user),
                'from'       => $timestampFrom->toIso8601String(),
                'to'         => $timestampTo->toIso8601String(),
                'exportedAt' => Carbon::now()->toIso8601String(),
            ],
            'data' => StatusExportResource::collection($preparedData),
        ];
    }
}
