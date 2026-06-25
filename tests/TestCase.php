<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUpTheTestEnvironment(): void
    {
        parent::setUpTheTestEnvironment();
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }
}
