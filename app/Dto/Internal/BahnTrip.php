<?php declare(strict_types=1);

namespace App\Dto\Internal;

use App\Enum\MotisCategory;
use App\Models\Operator;

readonly class BahnTrip
{
    public string         $tripId;
    public string         $direction;
    public string         $lineName;
    public ?string        $routeColor;
    public string         $number;
    public string         $category;
    public ?MotisCategory $mode;
    public string         $journeyNumber;
    public ?Operator      $operator;

    public function __construct(
        string         $tripId,
        string         $direction,
        string         $lineName,
        string         $number,
        string         $category,
        string         $journeyNumber,
        ?Operator      $operator = null,
        ?string        $routeColor = null,
        ?MotisCategory $mode = null
    ) {
        $this->tripId        = $tripId;
        $this->direction     = $direction;
        $this->lineName      = $lineName;
        $this->number        = $number;
        $this->category      = $category;
        $this->journeyNumber = $journeyNumber;
        $this->operator      = $operator;
        $this->routeColor    = $routeColor;
        $this->mode          = $mode;
    }
}
