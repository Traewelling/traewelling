<?php

namespace App\View\Components;

use App\Interfaces\ActionButtonComponentInterface;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MuteButton extends Component implements ActionButtonComponentInterface
{
    public User $user;
    public bool $dropdown;

    public function __construct(User $user, bool $dropdown = false) {
        $this->user     = $user;
        $this->dropdown = $dropdown;
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
}
