<?php

namespace App\View\Components;

class MuteButton extends AbstractUserActionButton
{
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
