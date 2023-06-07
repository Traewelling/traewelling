<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\Client as PassportClient;

class OAuthClient extends PassportClient {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'secret',
        'redirect',
        'privacy_policy_url',
        'personal_access_client',
        'password_client',
        'revoked',
        'created_at',
        'updated_at',
        'webhooks_enabled',
        'authorized_webhook_url',
    ];

    protected $casts = [
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
    ];

    public static function newFactory() {
        return parent::newFactory();
    }

    public function isConfidential(): bool {
        return $this->secret != null;
    }
}
