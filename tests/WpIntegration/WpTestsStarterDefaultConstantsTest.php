<?php

declare(strict_types=1);

namespace WpTestsStarter\Test\WpIntegration;

use PHPUnit\Framework\TestCase;
use WpTestsStarter\WpTestsStarter;

class WpTestsStarterDefaultConstantsTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testBoostrapWithDefaultConstants()
    {
        #$this->markTestIncomplete();

        $baseDir = dirname(dirname(__DIR__)) . '/vendor/inpsyde/wordpress-dev';
        $testee = new WpTestsStarter($baseDir);

        // defined in phpunit-integration.xml
        $testee->defineDbName(Db\NAME);
        $testee->defineDbUser(Db\USER);
        $testee->defineDbPassword(Db\PASSWORD);
        $testee->defineDbHost(Db\HOST);
        $testee->defineDbCharset(Db\CHARSET);
        $testee->defineDbCollate(Db\COLLATE);
        $testee->setTablePrefix(Db\TABLE_PREFIX);

        $testee->bootstrap();

        $this->assertFileExists($testee->getConfigFile());
        $config_data = file_get_contents($testee->getConfigFile());
        self::assertMatchesRegularExpression('~define\(\s\'ABSPATH\',\s\'[^\']+\'~', $config_data);
    }
}
