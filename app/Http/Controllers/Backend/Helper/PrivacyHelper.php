<?php

namespace App\Http\Controllers\Backend\Helper;

use App\Http\Controllers\Controller;
use InvalidArgumentException;

class PrivacyHelper extends Controller
{
    /**
     * @param string $ipAddress
     *
     * @return string
     */
    public static function maskIpAddress(string $ipAddress): string {
        $ipHex = inet_pton($ipAddress);

        if (strlen($ipHex) === 4) {
            //IPv4
            $ipHex[2] = "\x00";
            $ipHex[3] = "\x00";
            return inet_ntop($ipHex);
        }

        if (strlen($ipHex) === 16) {
            //IPv6
            for ($byte = 10; $byte <= 15; $byte++) {
                $ipHex[$byte] = "\x00";
            }
            return inet_ntop($ipHex);
        }
        throw new InvalidArgumentException();
    }
}
