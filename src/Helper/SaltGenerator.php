<?php

declare(strict_types=1);

namespace Inpsyde\WpTestsStarter\Helper;

class SaltGenerator
{
    /**
     * Generates random strings using all ascii chars between
     * 32 (Space) and 126 (~). Note that this is all but secure and
     * is only meant to be used during automated tests
     */
    public function generateSalt(int $length = 40): string
    {
        $salt = '';
        for ($i = 1; $i <= $length; $i++) {
            $salt .= chr(mt_rand(32, 126));
        }

        return $salt;
    }
}
