<?php

declare(strict_types=1);

namespace WpTestsStarter\Test\WpIntegration;

use PHPUnit\Framework\TestCase;
use WpTestsStarter\WpTestsStarter;

/**
 * Class WpTestsStarterTest
 *
 * This test has to be in a completely different environment as the Constants are alredy
 * defined by during unit tests
 *
 * @package WpTestsStarter\Test\Integration
 */
class WpTestsStarterTest extends TestCase
{
    private static string $baseDir;

    private static WpTestsStarter $testee;

    public static function setUpBeforeClass(): void
    {
        self::$baseDir = dirname(dirname(__DIR__)) . '/vendor/wordpress/wordpress';
        self::$testee = new WpTestsStarter(self::$baseDir);

        // defined in phpunit-integration.xml
        self::$testee->useDbName(Db\NAME);
        self::$testee->useDbUser(Db\USER);
        self::$testee->useDbPassword(Db\PASSWORD);
        self::$testee->useDbHost(Db\HOST);
        self::$testee->useDbCharset(Db\CHARSET);
        self::$testee->useDbCollation(Db\COLLATE);
        self::$testee->useTablePrefix(Db\TABLE_PREFIX);

        // test plugin loading
        $plugin_test_dir = dirname(__DIR__) . '/tmp';
        $test_plugin = 'plugin/test-plugin.php';
        self::$testee->useWpPluginDir($plugin_test_dir);
        self::$testee->addActivePlugin($test_plugin);
    }

    public function testBootstrap(): void
    {
        self::$testee->bootstrap();

        // test if the environment is available
        self::assertTrue(
            class_exists(\WP_UnitTestCase::class),
            'Class \WP_UnitTestCase does not exist.'
        );

        self::assertInstanceOf(
            \wpdb::class,
            $GLOBALS['wpdb']
        );

        $dbTables = $GLOBALS['wpdb']->get_results(
            'SHOW TABLES',
            \ARRAY_N
        );
        $tablesFlat = [];
        foreach ($dbTables as $row) {
            $tablesFlat[] = $row[0];
        }

        $optionTable = $GLOBALS['table_prefix'] . 'options';
        self::assertTrue(
            in_array($optionTable, $tablesFlat),
            "Table {$optionTable} does not exist!"
        );
    }

    /**
     * @depends testBootstrap
     */
    public function testSetActivePlugin(): void
    {
        self::assertArrayHasKey('wp_tests_options', $GLOBALS);
        self::assertArrayHasKey('active_plugins', $GLOBALS['wp_tests_options']);
        self::assertContains('plugin/test-plugin.php', $GLOBALS['wp_tests_options']['active_plugins']);

        //Todo: assert that the plugin really got loaded
    }
}
