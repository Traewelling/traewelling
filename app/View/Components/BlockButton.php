<?php

namespace App\View\Components;

class BlockButton extends AbstractUserActionButton
{

    public function getText(): string {
        return $this->user->isBlockedByAuthUser ? __('user.unblock-tooltip') : __('user.block-tooltip');
    }

    public function getRoute(): string {
        return $this->user->isBlockedByAuthUser ? route('user.unblock') : route('user.block');
    }

    public function getIcon(): string {
        return $this->user->isBlockedByAuthUser ? 'fa-unlock' : 'fa-ban';
    }
}
