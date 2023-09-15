<?php

namespace App\View\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EventRejectionButton extends Component
{
    public function render(): View|Factory|Application {
        return view('components.event-rejection-button');
    }
}
