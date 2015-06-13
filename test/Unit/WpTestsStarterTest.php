<?php # -*- coding: utf-8 -*-

namespace WpTestsStarter\Test\Unit;
use WpTestsStarter\WpTestsStarter;

class WpTestsStarterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @see WpTestsStarter::defineConst()
	 */
	public function testDefineConst() {

		$testee = new WpTestsStarter( '' );

		$testee->defineConst( 'FOO', 'Foo' );
		$this->assertTrue(
			defined( 'FOO' ),
			'Constant FOO is not defined but should.'
		);
		$this->assertEquals(
			'Foo',
			FOO
		);

		$testee->defineConst( __NAMESPACE__ . '\BAR', 'Bar' );
		$this->assertTrue(
			defined( __NAMESPACE__ . '\BAR' ),
			'Constant ' . __NAMESPACE__ . '\BAR is not defined but should.'
		);
		$this->assertEquals(
			'Bar',
			\WpTestsStarter\Test\Unit\BAR
		);

		// check that constants don't get overwrite
		$this->assertFalse(
			$testee->defineConst( 'FOO', 'Bazz' ),
			'WpTestsStarter::defineConst returned wrong value.'
		);
		$this->assertEquals(
			'Foo',
			FOO
		);
	}

	/**
	 * @see WpTestsStarter::defineAbspath()
	 */
	public function testDefineAbspath() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$testee->defineAbspath();

		$this->assertEquals(
			$baseDir . 'src/',
			\ABSPATH
		);
	}

	/**
	 * @see WpTestsStarter::defineDbName()
	 */
	public function testDefineDbName() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$dbName = 'wp-tests-starter';
		$testee->defineDbName( $dbName );

		$this->assertEquals(
			$dbName,
			\DB_NAME
		);
	}

	/**
	 * @see WpTestsStarter::defineDbHost()
	 */
	public function testDefineDbHost() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$dbHost = 'remote.host';
		$testee->defineDbHost( $dbHost );

		$this->assertEquals(
			$dbHost,
			\DB_HOST
		);
	}

	/**
	 * @see WpTestsStarter::defineDbUser()
	 */
	public function testDefineDbUser() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$dbUser = 'my-user';
		$testee->defineDbUser( $dbUser );

		$this->assertEquals(
			$dbUser,
			\DB_USER
		);
	}

	/**
	 * @see WpTestsStarter::defineDbUser()
	 */
	public function testDefineDbPassword() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$dbPassword = 'aku49l.ha83';
		$testee->defineDbPassword( $dbPassword );

		$this->assertEquals(
			$dbPassword,
			\DB_PASSWORD
		);
	}

	/**
	 * @see WpTestsStarter::setGlobal()
	 */
	public function testSetGlobal() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$var = 'foo';
		$value = 'bar';

		$this->assertArrayNotHasKey(
			$var,
			$GLOBALS
		);

		$testee->setGlobal( $var, $value );

		$this->assertEquals(
			$value,
			$GLOBALS[ $var ]
		);
	}

	/**
	 * @see WpTestsStarter::setTablePrefix()
	 */
	public function testSetTablePrefix() {

		$baseDir = '/path/to/wp-repo/';
		$testee = new WpTestsStarter( $baseDir );
		$tablePrefix = 'wp_';
		$testee->setTablePrefix( $tablePrefix );

		global $table_prefix;
		$this->assertEquals(
			$tablePrefix,
			$table_prefix
		);
	}

	/**
	 * @see WpTestsStarter::createDummyConfigFile()
	 */
	public function testCreateDummyConfigFile() {

		$baseDir = dirname( __DIR__ ) . '/tmp';
		$configFile = $baseDir . '/wp-tests-config.php';
		$testee = new WpTestsStarter( $baseDir );

		if ( file_exists( $configFile ) )
			unlink( $configFile );

		$this->assertFileNotExists(
			$configFile,
			"Remove the temporary config file before running this test."
		);

		$testee->createDummyConfigFile();

		$this->assertFileExists( $configFile );

		unlink( $configFile );
	}
}
 