<?php

namespace App\Rules;

use App\Enum\WebhookEvent;
use Illuminate\Contracts\Validation\InvokableRule;

class StringifiedWebhookEvents implements InvokableRule {
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail) {
        foreach (WebhookEvent::cases() as $event) {
            if ($event->name() == $value) {
                return;
            }
        }
        $fail("No matching webhook event: " . $value);
    }
}
