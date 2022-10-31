<?php

declare(strict_types=1);

namespace Inpsyde\WpTestsStarter\Test\WpIntegration;

use PHPUnit\Framework\TestCase;
use Inpsyde\WpTestsStarter\WpTestsStarter;

class WpTestsStarterTest extends TestCase
{
    private static string $wpBaseDir;

    private static string $pluginDir;

    private static string $testPlugin;

    public static function setUpBeforeClass(): void
    {
        self::$wpBaseDir = dirname(dirname(__DIR__)) . '/vendor/wordpress/wordpress';
        self::$pluginDir = dirname(__DIR__) . '/plugins';
        self::$testPlugin = 'test-plugin.php';
    }

    public function testBootstrap(): void
    {
        $testee = new WpTestsStarter(
            self::$wpBaseDir,
            getenv('WPTS_DB_URL')
        );
        $testee->useWpPluginDir(self::$pluginDir)
            ->addActivePlugin(self::$testPlugin)
            ->addLivePlugin(static function(): void {
                defined('WPTS_LIVE_PLUGIN_RUN') or define('WPTS_LIVE_PLUGIN_RUN', true);
            })
            ->bootstrap();

        // test if the environment is available
        self::assertTrue(
            class_exists(\WP_UnitTestCase::class),
            'Class \WP_UnitTestCase does not exist.'
        );

        $this->wpDbAssertions();
        $this->installedAssertions();
        $this->pluginAssertions();
        $this->livePluginAssertions();
    }

    private function wpDbAssertions(): void
    {
        self::assertInstanceOf(
            \wpdb::class,
            $GLOBALS['wpdb']
        );
        self::assertTrue(
            $GLOBALS['wpdb']->check_connection()
        );
    }

    private function installedAssertions(): void
    {
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

    private function pluginAssertions(): void
    {
        self::assertArrayHasKey(
            'wp_tests_options',
            $GLOBALS
        );
        self::assertArrayHasKey(
            'active_plugins',
            $GLOBALS['wp_tests_options']
        );
        self::assertContains(
            self::$testPlugin,
            $GLOBALS['wp_tests_options']['active_plugins']
        );
        self::assertTrue(
            defined('WPTS_TEST_PLUGIN_LOADED')
        );
    }

    private function livePluginAssertions(): void
    {
        self::assertFileExists(
            WPMU_PLUGIN_DIR . '/wp-tests-starter-live-plugin.php'
        );
        self::assertTrue(
            defined('WPTS_LIVE_PLUGIN_RUN')
        );
    }
}
