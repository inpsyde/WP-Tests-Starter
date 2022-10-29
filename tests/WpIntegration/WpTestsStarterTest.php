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
    /**
     * @type string
     */
    private static $baseDir;

    /**
     * @type WpTestsStarter
     */
    private static $testee;

    /**
     * runs once before all the tests
     */
    public static function setUpBeforeClass(): void
    {
        self::$baseDir = dirname(dirname(__DIR__)) . '/vendor/inpsyde/wordpress-dev';
        self::$testee = new WpTestsStarter(self::$baseDir);

        // defined in phpunit-integration.xml
        self::$testee->defineDbName(Db\NAME);
        self::$testee->defineDbUser(Db\USER);
        self::$testee->defineDbPassword(Db\PASSWORD);
        self::$testee->defineDbHost(Db\HOST);
        self::$testee->defineDbCharset(Db\CHARSET);
        self::$testee->defineDbCollate(Db\COLLATE);
        self::$testee->setTablePrefix(Db\TABLE_PREFIX);

        self::$testee->defineAbspath();
        self::$testee->definePhpBinary();
        self::$testee->defineWpLang();
        self::$testee->defineWpDebug();
        self::$testee->defineTestsDomain();
        self::$testee->defineTestsEmail();
        self::$testee->defineTestsTitle();

        // test plugin loading
        $plugin_test_dir = dirname(__DIR__) . '/tmp';
        $test_plugin = 'plugin/test-plugin.php';
        self::$testee->defineWpPluginDir($plugin_test_dir);
        self::$testee->setActivePlugin($test_plugin);
    }

    public function testSetUp()
    {
        self::assertNotEmpty(\DB_NAME);
        self::assertNotEmpty(\DB_USER);
        self::assertNotEmpty($GLOBALS['table_prefix']);

        $definedConstants = self::$testee->getDefinedConstants();

        self::assertArrayHasKey('DB_NAME', $definedConstants);
        self::assertArrayHasKey('DB_USER', $definedConstants);
    }

    /**
     * @see WpTestsStarter::createDummyConfigFile()
     */
    public function testCreateDummyConfigFile()
    {
        $configFile = self::$baseDir . '/wp-tests-config.php';
        if (file_exists($configFile)) {
            unlink($configFile);
        }

        self::$testee->createDummyConfigFile();
        $fileContent = file_get_contents($configFile);

        self::assertStringEndsWith(
            self::$testee->getDefinedConstantsCode(),
            $fileContent
        );

        $definedConstants = self::$testee->getDefinedConstants();
        foreach ($definedConstants as $name => $value) {
            $pattern = sprintf(
                "~define\(\s*'%s',\s*'%s'\s*\);~",
                preg_quote(self::$testee->escapePhpString($name)),
                preg_quote(self::$testee->escapePhpString($value))
            );
            self::assertMatchesRegularExpression(
                $pattern,
                $fileContent
            );
        }

        unlink($configFile);
    }

    /**
     * @see WpTestsStarter::bootstrap()
     */
    public function testBootstrap()
    {
        self::$testee->bootstrap();

        // test if the environment is available
        self::assertTrue(
            class_exists('\WP_UnitTestCase'),
            'Class \WP_UnitTestCase does not exist.'
        );

        self::assertInstanceOf(
            '\wpdb',
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

        $optionTable = Db\TABLE_PREFIX . 'options';
        self::assertTrue(
            in_array($optionTable, $tablesFlat),
            "Table {$optionTable} does not exist!"
        );
    }

    /**
     * @see     WpTestsStarter::setActivePlugin()
     * @depends testBootstrap
     */
    public function testSetActivePlugin()
    {
        self::markTestIncomplete("Needs to be fixed");
        /**
         * @see tmp/plugin/test-plugin.php
         */
        self::assertTrue(
            defined('WP_TEST_STARTER_TEST_PLUGIN'),
            'Test plugin file seemed not loaded.'
        );
    }
}
