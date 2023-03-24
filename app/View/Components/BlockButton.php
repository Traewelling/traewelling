<?php

namespace App\View\Components;

use App\Interfaces\ActionButtonComponentInterface;
use Illuminate\View\Component;

class BlockButton extends Component implements ActionButtonComponentInterface
{
    public $user;
    public $dropdown;

    public function __construct($user, $dropdown = false) {
        $this->user     = $user;
        $this->dropdown = $dropdown;
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
}
