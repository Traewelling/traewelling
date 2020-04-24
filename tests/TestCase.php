<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function createGDPRAckedUser(): User
    {

        // Creates user
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');

        return $user;
    }
}
