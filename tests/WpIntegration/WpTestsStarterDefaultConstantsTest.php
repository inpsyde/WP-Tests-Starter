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
    public function testBoostrapWithDefaultConstants(): void
    {
        $baseDir = dirname(dirname(__DIR__)) . '/vendor/wordpress/wordpress';
        $wpTestConfig = $baseDir . '/wp-tests-config.php';
        $testee = new WpTestsStarter($baseDir);

        // defined in phpunit-integration.xml
        $testee->useDbName(Db\NAME)
            ->useDbUser(Db\USER)
            ->useDbCharset(Db\USER)
            ->useDbPassword(Db\PASSWORD)
            ->useDbHost(Db\HOST)
            ->useDbCharset(Db\CHARSET)
            ->useDbCollation(Db\COLLATE)
            ->useTablePrefix(Db\TABLE_PREFIX);

        $testee->bootstrap();

        $this->assertFileExists($wpTestConfig);
        $config_data = file_get_contents($wpTestConfig);
        self::assertMatchesRegularExpression('~define\(\s\'ABSPATH\',\s\'[^\']+\'~', $config_data);
    }
}
