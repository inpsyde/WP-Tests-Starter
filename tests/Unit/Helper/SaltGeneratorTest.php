<?php

declare(strict_types=1);

namespace Inpsyde\WpTestsStarter\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Inpsyde\WpTestsStarter\Helper;

class SaltGeneratorTest extends TestCase
{
    /**
     * @dataProvider generateSaltTestProvider
     */
    public function testGenerateSalt(int $length): void
    {
        $testee = new Helper\SaltGenerator();

        $salt = $testee->generateSalt($length);

        self::assertMatchesRegularExpression(
            '~^[\x20-\x7E]{' . preg_quote((string)$length) . '}$~',
            $salt
        );
    }

    public function generateSaltTestProvider(): array
    {
       return [
           ['length' => 1],
           ['length' => 10],
           ['length' => 42],
       ];
    }
}
