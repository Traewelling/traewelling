<?php
declare(strict_types=1);

namespace App\Interfaces;

interface IconEnumInterface
{
    public function faIcon(): string;

    public function title(): string;

    public function description(): string;
}
