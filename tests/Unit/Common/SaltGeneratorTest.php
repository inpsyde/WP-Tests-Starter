<?php

declare(strict_types=1);

namespace WpTestsStarter\Test\Unit\Common;

use PHPUnit\Framework\TestCase;
use WpTestsStarter\Helper;

class SaltGeneratorTest extends TestCase
{
    /**
     * @dataProvider generateSaltTestProvider
     * @param int $length
     * @see          SaltGenerator::generateSalt()
     */
    public function testGenerateSalt($length)
    {
        $testee = new Helper\SaltGenerator();

        $salt = $testee->generateSalt($length);

        self::assertMatchesRegularExpression(
            '~^[\x20-\x7E]{' . preg_quote((string) $length) . '}$~',
            $salt
        );
    }

    /**
     * @return array
     */
    public function generateSaltTestProvider()
    {
        $data = [];

        # 0:
        $data[] = [
            1,
        ];

        # 2:
        $data[] = [
            10,
        ];

        #3
        $data[] = [
            42,
        ];

        return $data;
    }
}
