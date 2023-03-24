<?php

namespace App\Interfaces;

use App\Models\User;

interface UserActionButtonComponentInterface
{
    public function getText(): string;

    public function getRoute(): string;

    public function getIcon(): string;

    public function getUser(): User;

    public function showText(): bool;

    public function isDropdown(): bool;
}
