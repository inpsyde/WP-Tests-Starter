<?php

declare(strict_types=1);

namespace WpTestsStarter\Common;

class SaltGenerator
{
    /**
     * Generates random strings using all ascii chars between
     * 32 (Space) and 126 (~)
     *
     * @param int $length
     * @return string
     */
    public function generateSalt($length = 40)
    {
        $salt = '';
        for ($i = 1; $i <= $length; $i++) {
            $salt .= chr(mt_rand(32, 126));
        }

        return $salt;
    }
}
