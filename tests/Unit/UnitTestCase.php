<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class UnitTestCase extends BaseTestCase
{
    use CreatesApplication;
}
