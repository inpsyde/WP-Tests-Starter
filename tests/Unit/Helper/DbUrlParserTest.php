<?php

declare(strict_types=1);

namespace Inpsyde\WpTestsStarter\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Inpsyde\WpTestsStarter\Exception\RuntimeException;
use Inpsyde\WpTestsStarter\Helper\DbUrlParser;


class DbUrlParserTest extends TestCase
{

    /**
     * @dataProvider parseData
     */
    public function testParse(string $dbUrl, array $expected): void
    {
        self::assertEquals(
            $expected,
            (new DbUrlParser())->parse($dbUrl)
        );
    }

    /**
     * @see testParse
     */
    public function parseData(): iterable
    {

        yield 'Test entire parameter set' => [
            'dbUrl' => 'mysql://john:T0p3ecr3t@localhost/wordpress?table_prefix=wp_tests_&charset=iso88591&collation=latin1_swedish_ci',
            'expected' => [
                'user' => 'john',
                'host' => 'localhost',
                'password' => 'T0p3ecr3t',
                'db' => 'wordpress',
                'table_prefix' => 'wp_tests_',
                'charset' => 'iso88591',
                'collation' => 'latin1_swedish_ci'
            ]
        ];

        yield 'Only user and host' => [
            'dbUrl' => 'mysql://john@localhost',
            'expected' => [
                'user' => 'john',
                'host' => 'localhost',
                'password' => null,
                'db' => null,
                'table_prefix' => null,
                'charset' => null,
                'collation' => null,
            ]
        ];

        yield 'Nothing at all' => [
            'dbUrl' => 'mysql:',
            'expected' => [
                'user' => null,
                'host' => null,
                'password' => null,
                'db' => null,
                'table_prefix' => null,
                'charset' => null,
                'collation' => null,
            ]
        ];
    }

    /**
     * @dataProvider parseThrowsExceptionData
     */
    public function testParseThrowsException(string $url, string $expectedMessage): void
    {
        $testee = new DbUrlParser();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage($expectedMessage);

        $testee->parse($url);
    }

    /**
     * @see testParseThrowsException
     */
    public function parseThrowsExceptionData(): iterable
    {

        yield 'Test invalid URL' => [
            'mysql://',
            "Please provide a valid dbUrl",
        ];

        yield 'Test unsupported scheme' => [
            'mssql:',
            "Currently only 'mysql' databases are supported",
        ];
    }
}
