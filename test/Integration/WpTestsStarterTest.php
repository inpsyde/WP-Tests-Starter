<?php # -*- coding: utf-8 -*-

namespace WpTestsStarter\Test\Integration;
use WpTestsStarter\WpTestsStarter;

/**
 * Class WpTestsStarterTest
 *
 * This test has to be in a completely different environment as the Constants are alredy
 * defined by during unit tests
 *
 * @package WpTestsStarter\Test\Integration
 */
class WpTestsStarterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @type string
	 */
	private $baseDir;

	/**
	 * @type WpTestsStarter
	 */
	private $testee;

	/**
	 * runs before each test
	 */
	public function setUp() {


		$this->baseDir = dirname( dirname( __DIR__ ) ) . '/vendor/inpsyde/wordpress-dev';
		$this->testee = new WpTestsStarter( $this->baseDir );

		// defined in phpunit-integration.xml
		$this->testee->defineDbName( Db\NAME );
		$this->testee->defineDbUser( Db\USER );
		$this->testee->defineDbPassword( Db\PASSWORD );
		$this->testee->defineDbHost( Db\HOST );
		$this->testee->defineDbCharset( Db\CHARSET );
		$this->testee->defineDbCollate( Db\COLLATE );
		$this->testee->setTablePrefix( Db\TABLE_PREFIX );

	}

	public function testSetUp() {

		$this->assertNotEmpty( \DB_NAME );
		$this->assertNotEmpty( \DB_USER );
		$this->assertNotEmpty( $GLOBALS[ 'table_prefix' ] );
	}

	/**
	 * @see WpTestsStarter::bootstrap()
	 */
	public function testBootstrap() {


		$this->markTestSkipped( 'Under construction' );

		/**
		 * Todo: Find out why this throws a 'Headers already sent' error
		 *
		 * wp-tests-starter/vendor/inpsyde/wordpress-dev/src/wp-includes/pluggable.php:1196
		 * wp-tests-starter/vendor/inpsyde/wordpress-dev/src/wp-includes/load.php:483
		 * wp-tests-starter/vendor/inpsyde/wordpress-dev/src/wp-settings.php:109
		 * wp-tests-starter/vendor/inpsyde/wordpress-dev/tests/phpunit/includes/bootstrap.php:85
		 * wp-tests-starter/src/WpTestsStarter/WpTestsStarter.php:56
		 * wp-tests-starter/test/Integration/WpTestsStarterTest.php:58
		 */
		$this->testee->bootstrap();
	}
}
 