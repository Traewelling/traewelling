<?php

namespace Tests\Unit;

use Tests\TestCase;
use App;

class HelperMethodTest extends TestCase
{
    public function testNumberFunction() {
        App::setLocale('de');
        
        // Thousands with a rounding decimal
        $this->assertEquals("1.000,23", number(1000.234));

        // Deal with small numbers
        $this->assertEquals("0,00", number(0));
        $this->assertEquals("0,00", number(0.001));
        $this->assertEquals("0,01", number(0.0099));

        // if n \in \N, add dummy nulls after the null.
        $this->assertEquals("1,00", number(1));

        // What about large numbers?
        $this->assertEquals("4.294.967.296,00", number(2**32));

        // What about negative numbers?
        $this->assertEquals("0,00", number(-0));
        $this->assertEquals("-1,00", number(-1));
        $this->assertEquals("-4.294.967.296,00", number(-1* 2**32));
    }
}
