<?php

declare(strict_types=1);

namespace App\Interfaces;

interface IconEnumInterface
{
    public function title(): ?string;

    public function description(): ?string;
}
