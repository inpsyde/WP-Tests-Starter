<?php

declare(strict_types=1);

namespace WpTestsStarter\Test\Unit\Common;

use WpTestsStarter\Common;

class SaltGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider generateSaltTestProvider
     * @param int $length
     * @see          SaltGenerator::generateSalt()
     */
    public function testGenerateSalt($length)
    {
        $testee = new Common\SaltGenerator();

        $salt = $testee->generateSalt($length);

        $this->assertRegExp(
            '~^[\x20-\x7E]{' . preg_quote($length) . '}$~',
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
