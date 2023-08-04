<?php

namespace App\View\Components;

use App\Interfaces\UserActionButtonComponentInterface;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

abstract class AbstractUserActionButton extends Component
{
    public User $user;
    public bool $dropdown;
    public bool $showText;
    public bool $disabled;

    public function __construct(User $user, bool $showText = false, bool $disabled = false, bool $dropdown = false) {
        $this->user     = $user;
        $this->dropdown = $dropdown;
        $this->showText = $showText;
        $this->disabled = $disabled;
    }

    public function render(): View {
        return view('components.user-action-button');
    }

    abstract public function getText(): string;

    abstract public function getRoute(): string;

    abstract public function getIcon(): string;

    public function getUser(): User {
        return $this->user;
    }

    public function showText(): bool {
        return $this->dropdown || $this->showText;
    }

    public function isDropdown(): bool {
        return $this->dropdown;
    }

    public function isDisabled(): bool {
        return $this->disabled;
    }
}
