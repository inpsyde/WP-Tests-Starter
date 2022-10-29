<?php

declare(strict_types=1);

namespace WpTestsStarter\Test\Unit;

use PHPUnit\Framework\TestCase;
use WpTestsStarter\WpTestsStarter;

class WpTestsStarterTest extends TestCase
{
    /**
     * @see WpTestsStarter::defineConst()
     */
    public function testDefineConst()
    {
        $testee = new WpTestsStarter('');

        $testee->defineConst('FOO', 'Foo');
        self::assertTrue(
            defined('FOO'),
            'Constant FOO is not defined but should.'
        );
        self::assertSame(
            'Foo',
            FOO
        );

        $testee->defineConst(__NAMESPACE__ . '\BAR', 'Bar');
        self::assertTrue(
            defined(__NAMESPACE__ . '\BAR'),
            'Constant ' . __NAMESPACE__ . '\BAR is not defined but should.'
        );
        self::assertSame(
            'Bar',
            \WpTestsStarter\Test\Unit\BAR
        );

        // check that constants don't get overwrite
        self::assertFalse(
            $testee->defineConst('FOO', 'Bazz'),
            'WpTestsStarter::defineConst returned wrong value.'
        );
        self::assertSame(
            'Foo',
            FOO
        );
    }

    /**
     * @see WpTestsStarter::getDefinedConstants()
     */
    public function testGetDefinedConstants()
    {
        $testee = new WpTestsStarter('');

        $const = strtoupper(__FUNCTION__);
        $value = __METHOD__;
        $testee->defineConst($const, $value);

        $definedConstants = $testee->getDefinedConstants();
        self::assertArrayHasKey(
            $const,
            $definedConstants
        );
        self::assertSame(
            $value,
            $definedConstants[$const]
        );
    }

    public function testGetDefinedConstantsCode()
    {
        $testee = new WpTestsStarter('');

        $const = strtoupper(__FUNCTION__);
        $value = __METHOD__;
        $testee->defineConst($const, $value);

        $definedConstantsCode = $testee->getDefinedConstantsCode();

        $expectedConst = $testee->escapePhpString($const);
        $expectedValue = $testee->escapePhpString($value);

        $expectPattern = sprintf(
            "~define\(\s*'%s',\s*'%s'\s*\);~",
            preg_quote($expectedConst),
            preg_quote($expectedValue)
        );

        self::assertMatchesRegularExpression(
            $expectPattern,
            $definedConstantsCode
        );
    }

    /**
     * @see WpTestsStarter::defineAbspath()
     */
    public function testDefineAbspath()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $testee->defineAbspath();

        self::assertSame(
            $baseDir . 'src/',
            \ABSPATH
        );
    }

    /**
     * @see WpTestsStarter::defineDbName()
     */
    public function testDefineDbName()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $dbName = 'wp-tests-starter';
        $testee->defineDbName($dbName);

        self::assertSame(
            $dbName,
            \DB_NAME
        );
    }

    /**
     * @see WpTestsStarter::defineDbHost()
     */
    public function testDefineDbHost()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $dbHost = 'remote.host';
        $testee->defineDbHost($dbHost);

        self::assertSame(
            $dbHost,
            \DB_HOST
        );
    }

    /**
     * @see WpTestsStarter::defineDbUser()
     */
    public function testDefineDbUser()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $dbUser = 'my-user';
        $testee->defineDbUser($dbUser);

        self::assertSame(
            $dbUser,
            \DB_USER
        );
    }

    /**
     * @see WpTestsStarter::defineDbUser()
     */
    public function testDefineDbPassword()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $dbPassword = 'aku49l.ha83';
        $testee->defineDbPassword($dbPassword);

        self::assertSame(
            $dbPassword,
            \DB_PASSWORD
        );
    }

    /**
     * @see WpTestsStarter::defineWpPluginDir()
     */
    public function testDefineWpPluginDir()
    {
        $testee = new WpTestsStarter('');
        $pluginDir = __DIR__;
        $testee->defineWpPluginDir($pluginDir);

        self::assertTrue(
            defined('\WP_PLUGIN_DIR')
        );
        self::assertSame(
            $pluginDir,
            \WP_PLUGIN_DIR
        );
    }

    public function testActivatePlugin()
    {
        $testee = new WpTestsStarter('');
        $plugin = 'my/plugin.php';
        $testee->setActivePlugin($plugin);

        self::assertContains(
            $plugin,
            $GLOBALS['wp_tests_options']['active_plugins']
        );
    }

    /**
     * @see WpTestsStarter::setGlobal()
     */
    public function testSetGlobal()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $var = 'foo';
        $value = 'bar';

        $this->assertArrayNotHasKey(
            $var,
            $GLOBALS
        );

        $testee->setGlobal($var, $value);

        self::assertSame(
            $value,
            $GLOBALS[$var]
        );
    }

    /**
     * @see WpTestsStarter::setTablePrefix()
     */
    public function testSetTablePrefix()
    {
        $baseDir = '/path/to/wp-repo/';
        $testee = new WpTestsStarter($baseDir);
        $tablePrefix = 'wp_';
        $testee->setTablePrefix($tablePrefix);

        global $table_prefix;
        self::assertSame(
            $tablePrefix,
            $table_prefix
        );
    }

    /**
     * @see WpTestsStarter::createDummyConfigFile()
     */
    public function testCreateDummyConfigFile()
    {
        $baseDir = dirname(__DIR__) . '/tmp';
        $configFile = $baseDir . '/wp-tests-config.php';
        $testee = new WpTestsStarter($baseDir);

        $const = strtoupper(__FUNCTION__);
        $testee->defineConst($const, 'FOO');
        $testee->defineConst($const . '_1', 'FOO');
        $testee->defineConst($const . '_2', 'FOO');

        if (file_exists($configFile)) {
            unlink($configFile);
        }

        self::assertFileDoesNotExist(
            $configFile,
            "Remove the temporary config file before running this test."
        );

        $testee->createDummyConfigFile();

        self::assertFileExists($configFile);
        $fileContent = file_get_contents($configFile);

        # count the number of definitions in the file
        $replaceCount = 0;
        str_replace('define(', 'define(', $fileContent, $replaceCount);
        self::assertSame(
            3,
            $replaceCount
        );

        unlink($configFile);
    }
}
