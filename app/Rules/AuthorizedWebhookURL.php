<?php

namespace App\Rules;

use App\Models\OAuthClient;
use Illuminate\Contracts\Validation\Rule;

class AuthorizedWebhookURL implements Rule
{
    protected $client;
    protected $message = '';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(OAuthClient $client)
    {
        $this->client = $client;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $authorizedUrl = $this->client->authorized_webhook_url;
        if ($value != $authorizedUrl) {
            $this->message = "URL does not match stored webhook url.";
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
