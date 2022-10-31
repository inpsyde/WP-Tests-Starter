<?php

declare(strict_types=1);

namespace WpTestsStarter\Test\WpIntegration;

use PHPUnit\Framework\TestCase;
use WpTestsStarter\Helper\DbUrlParser;
use WpTestsStarter\WpTestsStarter;

class WpTestsStarterDefaultConstantsTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testBoostrapWithDbUrl(): void
    {
        $baseDir = dirname(dirname(__DIR__)) . '/vendor/wordpress/wordpress';
        $wpTestConfig = $baseDir . '/wp-tests-config.php';
        $testee = new WpTestsStarter(
            $baseDir,
            getenv('WPTS_DB_URL')
        );
        $testee->bootstrap();

        $this->assertFileExists($wpTestConfig);
        $config_data = file_get_contents($wpTestConfig);

        self::assertMatchesRegularExpression(
            '~define\(\s\'ABSPATH\',\s\'[^\']+\'~',
            $config_data
        );
        self::assertInstanceOf(
            \wpdb::class,
            $GLOBALS['wpdb']
        );
        self::assertTrue(
            $GLOBALS['wpdb']->check_connection()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testBoostrapWithSetters(): void
    {
        $baseDir = dirname(dirname(__DIR__)) . '/vendor/wordpress/wordpress';
        $wpTestConfig = $baseDir . '/wp-tests-config.php';

        $credentials = (new DbUrlParser())
            ->parse(getenv('WPTS_DB_URL'));

        $testee = new WpTestsStarter($baseDir);

        $credentials['host'] and $testee->useDbHost($credentials['host']);
        $credentials['user'] and $testee->useDbUser($credentials['user']);
        $credentials['password'] and $testee->useDbPassword($credentials['password']);
        $credentials['db'] and $testee->useDbName($credentials['db']);
        $credentials['table_prefix'] and $testee->useTablePrefix($credentials['table_prefix']);
        $credentials['charset'] and $testee->useDbCharset($credentials['charset']);
        $credentials['collation'] and $testee->useDbCollation($credentials['collation']);
        $testee->bootstrap();

        $this->assertFileExists($wpTestConfig);
        $config_data = file_get_contents($wpTestConfig);

        self::assertMatchesRegularExpression(
            '~define\(\s\'ABSPATH\',\s\'[^\']+\'~',
            $config_data
        );
        self::assertInstanceOf(
            \wpdb::class,
            $GLOBALS['wpdb']
        );
        self::assertTrue(
            $GLOBALS['wpdb']->check_connection()
        );
    }
}
