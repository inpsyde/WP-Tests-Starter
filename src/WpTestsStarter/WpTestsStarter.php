<?php # -*- coding: utf-8 -*-

namespace WpTestsStarter;

class WpTestsStarter {

	/**
	 * @type string
	 */
	private $baseDir;

	/**
	 * @type Common\SaltGeneratorInterface
	 */
	private $saltGenerator;

	/**
	 * list of all by this class defined constants
	 *
	 * @type array
	 */
	private $definedConstants = array();

	/**
	 * Pass the absolute path of the wordpress-dev package here.
	 * It is "$baseDir/vendor/inpsyde/wordpress-dev" if you're using
	 * the inpsyde/wordpress-dev package
	 *
	 * @param string $baseDir
	 * @param Common\SaltGeneratorInterface $saltGenerator
	 */
	public function __construct( $baseDir, Common\SaltGeneratorInterface $saltGenerator = NULL ) {

		$this->baseDir = rtrim( $baseDir, '\\/' );
		if ( ! $saltGenerator )
			$saltGenerator = new Common\SaltGenerator;
		$this->saltGenerator = $saltGenerator;
	}

	/**
	 * Loading the WordPress testing bootstrap
	 */
	public function bootstrap() {

		$this->createDummyConfigFile();

		// define required constants if they not exists
		$this->defineDbHost();
		$this->defineDbCharset();
		$this->defineDbCollate();

		$this->defineTestsDomain();
		$this->defineTestsEmail();
		$this->defineTestsTitle();

		$this->defineWpLang();
		$this->definePhpBinary();
		$this->defineWpDebug();

		$this->defineAbspath();

		$wpBoostrapFile = $this->baseDir . '/tests/phpunit/includes/bootstrap.php';
		require_once $wpBoostrapFile;
	}

	/**
	 * Define a given constant if it not already exists
	 *
	 * @param string $const
	 * @param mixed $value
	 * @return bool
	 */
	public function defineConst( $const, $value ) {

		if ( defined( $const ) )
			return FALSE;

		$this->definedConstants[ $const ] = $value;
		return define( $const, $value );
	}

	/**
	 * @param string $abspath
	 */
	public function defineAbspath( $abspath = '' ) {

		if ( empty( $abspath ) ) {
			$abspath = $this->baseDir . '/src/';
		}
		$this->defineConst( 'ABSPATH', $abspath );
	}

	/**
	 * @param string $dbName
	 */
	public function defineDbName( $dbName ) {

		$this->defineConst( 'DB_NAME', $dbName );
	}

	/**
	 * @param string $dbHost
	 */
	public function defineDbHost( $dbHost = 'localhost' ) {

		$this->defineConst( 'DB_HOST', $dbHost );
	}

	/**
	 * @param string $dbUser
	 */
	public function defineDbUser( $dbUser ) {

		$this->defineConst( 'DB_USER', $dbUser );
	}

	/**
	 * @param string $dbPassword
	 */
	public function defineDbPassword( $dbPassword ) {

		$this->defineConst( 'DB_PASSWORD', $dbPassword );
	}

	/**
	 * @param string $dbCharset
	 */
	public function defineDbCharset( $dbCharset = 'utf8' ) {

		$this->defineConst( 'DB_CHARSET', $dbCharset );
	}

	/**
	 * @param string $dbCollate
	 */
	public function defineDbCollate( $dbCollate = '' ) {

		$this->defineConst( 'DB_COLLATE', $dbCollate );
	}

	/**
	 * @param bool $wpDebug
	 */
	public function defineWpDebug( $wpDebug = FALSE ) {

		$this->defineConst( 'WP_DEBUG', (bool) $wpDebug );
	}

	/**
	 * define the security keys and salts
	 */
	public function defineSalts() {

		$saltConstants = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
			'AUTH_KEY'
		);

		foreach ( $saltConstants as $constant ) {
			$this->defineConst(
				$constant,
				$this->saltGenerator->generateSalt()
			);
		}
	}

	/**
	 * @param string $domain
	 */
	public function defineTestsDomain( $domain = 'example.org' ) {

		$this->defineConst( 'WP_TESTS_DOMAIN', $domain );
	}

	/**
	 * @param string $email
	 */
	public function defineTestsEmail( $email = 'admin@example.org' ) {

		$this->defineConst( 'WP_TESTS_EMAIL', $email );
	}

	/**
	 * @param string $title
	 */
	public function defineTestsTitle( $title = 'Test Blog' ) {

		$this->defineConst( 'WP_TESTS_TITLE', $title );
	}

	/**
	 * @param string $binary
	 */
	public function definePhpBinary( $binary = 'php' ) {

		$this->defineConst( 'WP_PHP_BINARY', $binary );
	}

	/**
	 * @param string $lang
	 */
	public function defineWpLang( $lang = '' ) {

		$this->defineConst( 'WPLANG', $lang );
	}

	/**
	 * @param bool $flag
	 */
	public function defineTestForceKnownBugs( $flag ) {

		$this->defineConst( 'WP_TESTS_FORCE_KNOWN_BUGS', (bool) $flag );
	}

	/**
	 * @param $flag
	 */
	public function defineTestMultisite( $flag ) {

		$this->defineConst( 'WP_TESTS_MULTISITE', (bool) $flag );
	}

	/**
	 * @param string $prefix
	 */
	public function setTablePrefix( $prefix = 'wptests_' ) {

		$var = 'table_prefix';
		$this->setGlobal( $var, $prefix );
	}

	/**
	 * @param $var
	 * @param $value
	 */
	public function setGlobal( $var, $value ) {

		$GLOBALS[ $var ] = $value;
	}

	/**
	 * the WordPress bootstrap process does not allow
	 * to define a custom path of the config but looks
	 * for this file. so we create just an empty one.
	 */
	public function createDummyConfigFile() {

		$configFile = $this->baseDir . '/wp-tests-config.php';
		if ( ! file_exists( $configFile ) )
			touch( $configFile );

		/**
		 * the WordPress testing bootstrap requires the definitions
		 * of all these content in exactly this file, there's no way
		 * to dynamically define these constants as
		 * tests/phpunit/includes/bootstrap.php triggers a system() call to
		 * tests/phpunit/includes/install.php with a static path to the
		 * config file
		 */
		$constantsDefinition = $this->getDefinedConstantsCode();
		$content = <<<PHP
<?php
global \$table_prefix;
\$GLOBALS[ 'table_prefix' ] = "{$GLOBALS[ 'table_prefix' ]}";
{$constantsDefinition}
PHP;
		file_put_contents( $configFile, $content, LOCK_EX );

	}

	/**
	 * @return array
	 */
	public function getDefinedConstants() {

		return $this->definedConstants;
	}

	/**
	 * that feels so ugly
	 */
	public function getDefinedConstantsCode() {

		$code = '';
		foreach ( $this->definedConstants as $constant => $value ) {
			$constant = $this->escapePhpString( $constant );
			$value     = $this->escapePhpString( $value );
			$code .= "if ( ! defined( '{$constant}' ) )\n";
			$code .= "\tdefine( '{$constant}', '{$value}' );\n";
		}

		return $code;
	}

	/**
	 * that feels even more ugly
	 */
	public function escapePhpString( $value ) {

		$value = str_replace(
			array( '<?php', '<?', '?>' ),
			'',
			$value
		);
		$value = addcslashes( $value, "'\\" );

		return $value;
	}
}