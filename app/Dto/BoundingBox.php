<?php

namespace App\Dto;

use Illuminate\Contracts\Support\Arrayable;

class BoundingBox implements Arrayable
{
    public Coordinate $upperLeft;
    public Coordinate $lowerRight;

    public function __construct(Coordinate $topLeft, Coordinate $bottomRight)
    {
        $this->upperLeft = $topLeft;
        $this->lowerRight = $bottomRight;
    }

    public function toArray(): array
    {
        return [
            'topLeft' => $this->upperLeft->toArray(),
            'bottomRight' => $this->lowerRight->toArray()
        ];
    }
}
