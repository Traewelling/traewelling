<?php

namespace App\Interfaces;

interface ActionButtonComponentInterface
{
    public function getText(): string;

    public function getRoute(): string;

    public function getIcon(): string;
}
