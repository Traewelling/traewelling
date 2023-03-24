<?php

namespace App\View\Components;

use App\Interfaces\UserActionButtonComponentInterface;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MuteButton extends Component implements UserActionButtonComponentInterface
{
    public User $user;
    public bool $dropdown;
    public bool $showText;

    public function __construct(User $user, bool $showText = false, bool $dropdown = false) {
        $this->user     = $user;
        $this->dropdown = $dropdown;
        $this->showText = $showText;
    }

    public function render(): View|string|Closure {
        return view('components.user-action-button');
    }

    public function getText(): string {
        return $this->user->muted ? __('user.unmute-tooltip') : __('user.mute-tooltip');
    }

    public function getRoute(): string {
        return $this->user->muted ? route('user.unmute') : route('user.mute');
    }

    public function getIcon(): string {
        return $this->user->muted ? 'fa-eye' : 'fa-eye-slash';
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
