<?php

declare(strict_types=1);

namespace WpTestsStarter\Common;

interface SaltGeneratorInterface
{
    /**
     * @param int $length
     * @return string
     */
    public function generateSalt($length);
}
