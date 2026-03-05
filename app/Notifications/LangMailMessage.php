<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Traits\Localizable;

class LangMailMessage extends MailMessage
{
    use Localizable;

    public function __construct(string $locale)
    {
        $this->locale($locale);
    }

    public function locale(string $locale): self
    {
        $this->viewData['locale'] = $locale;

        return $this;
    }

    public function render()
    {
        return $this->withLocale($this->viewData['locale'], function () {
            return parent::render();
        });
    }
}
