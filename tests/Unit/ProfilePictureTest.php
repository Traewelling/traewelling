<?php

namespace Tests\Unit;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Tests\TestCase;

class ProfilePictureTest extends TestCase
{
    /**
     * @dataProvider pictureColorProvider
     */
    public function testPictureColor($avatar) {
        $this->assertEquals(6, strlen(ProfilePictureController::generateBackgroundHash($avatar)));
        $this->assertTrue(preg_match('^(?:[0-9a-fA-F]{3}){1,2}$^', ProfilePictureController::generateBackgroundHash($avatar)) === 1);
    }

    public static function pictureColorProvider(): array {
        return [
            ['abc'],
            ['123'],
            ['def'],
            ['ghi'],
            ['jkl'],
            ['smo'],
            ['lev'],
            ['kri'],
            ['jan'],
            ['Gertrud123'],
            ['NSL7O0dGsHDhCPUnYmGTbK45U'],
            ['oofRqlWTLFSxfXnjR7jdBmzTH'],
            ['e2SQy98c3pRnJrtn0dXoXZoJ1'],
            ['2eBrrT0m66Di3o4yR9Hyj4l8j'],
            ['ysHfah4aXFLXCYkjEkl13KJl4'],
            ['cvWpuod8rgbZmPW90ZjSaRYzv'],
            ['SaA1H8exzIJATOVMRt1Xn1wPN'],
            ['5FFNBwZ6lDoWnclnQloKzlEI1'],
            ['YbYjqAcBPFOGg07dn5BBzGiR8'],
            ['BMBF03Z4lZ6jiZeoBx4bLVwVD'],
            ['ZzZzZzZzZzZzZzZzZzZzZzZz'],
            ['a'],
            ['12345567890'],
            ['0183001070152575565536994'],
            ['1459906113875792749110709'],
            ['3500338114028935353047044'],
            ['8597671841203277442072412'],
            ['2767174654424651259235817'],
            ['4663050011717768327727543'],
            ['9155868068395293964592925'],
            ['9474153380008860014041008'],
            ['8236625554278537130711098'],
        ];
    }
}
