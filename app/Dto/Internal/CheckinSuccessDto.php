<?php

declare(strict_types=1);

namespace App\Dto\Internal;

use App\Dto\PointCalculation;
use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;

readonly class CheckinSuccessDto
{
    public Status           $status;
    public PointCalculation $pointCalculation;
    /**
     * @var Collection<Status>
     */
    public Collection $alsoOnThisConnection;

    public function __construct(Status $status, PointCalculation $pointCalculation, Collection $alsoOnThisConnection) {
        $this->status               = $status;
        $this->pointCalculation     = $pointCalculation;
        $this->alsoOnThisConnection = $alsoOnThisConnection;
    }
}
