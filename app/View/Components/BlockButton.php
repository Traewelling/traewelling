<?php

namespace App\View\Components;

use App\Interfaces\UserActionButtonComponentInterface;
use App\Models\User;
use Illuminate\View\Component;

class BlockButton extends Component implements UserActionButtonComponentInterface
{
    public User $user;
    public bool $dropdown;
    public bool $showText;

    public function __construct(User $user, bool $showText = false, bool $dropdown = false) {
        $this->user     = $user;
        $this->dropdown = $dropdown;
        $this->showText = $showText;
    }

    public function render() {
        return view('components.user-action-button');
    }

    public function getText(): string {
        return $this->user->isBlockedByAuthUser ? __('user.unblock-tooltip') : __('user.block-tooltip');
    }

    public function getRoute(): string {
        return $this->user->isBlockedByAuthUser ? route('user.unblock') : route('user.block');
    }

    public function getIcon(): string {
        return $this->user->isBlockedByAuthUser ? 'fa-unlock' : 'fa-ban';
    }

    public function getUser(): User {
        return $this->user;
    }

    public function showText(): bool {
        return $this->dropdown || $this->showText;
    }

    public function isDropdown(): bool {
        return $this->dropdown;
    }
}
