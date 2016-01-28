# WP Tests Starter
This package provides an easy and reproducible way to set up _integration tests_ for WordPress plugins and packages
using composer. It allows you to configure your tests solely within a `phpunit.xml` file.

Make sure you read the [Security notice](#security-notice) as well as [How it works](#how-it-works)!

Table of contents
 * [Quick start](#quick-start)
 * [Complete example](#complete-example)
 * [API reference](#api-reference)
 * [How it works](#how-it-works)
 * [Security notice](#security-notice)
 * [License](#license)
 * [Created by Inpsyde](#created-by-inpsyde)

## Quick start

1) Install the WordPress developer package in the version you want to test with (e.g. the 4.4 branch):

```
$ composer require inpsyde/wordpress-dev:~4.4 --dev --prefer-dist
```
(Suggesting you use the `--prefer-dist` option because the package is really large.)

2) Install WP Tests Starter:

```
$ composer require inpsyde/wp-tests-starter:~1.0 --dev
```

3) Setup the minimum required parameter (the database credentials) and run the bootstrap:

```php
<?php
use
	WpTestsStarter\WpTestsStarter;

// The full path to the wordpress package
$starter = new WpTestsStarter( '/path/to/your-project/vendor/inpsyde/wordpress-dev' );

// Minimum required settings
$starter->defineDbName( 'your-test-db' );
$starter->defineDbUser( 'your-test-user' );
$starter->defineDbPassword( 'your-test-user' );
$starter->setTablePrefix( 'your_table_prefix_' );

// This will finally include the WordPress test bootstrap
$starter->bootstrap();
```

## Complete example

Assuming you want to setup WordPress integration tests (using PHPUnit) for your plugin, that has the following directory structure:

```
some_plugin/
	assets/
	src/
		SomePluginCode.php
	tests/
		WpIntegration/
			SomePluginCodeTest.php
		bootstrap.php
	vendor/
		autoload.php
		...
	some_plugin.php
	composer.json
	composer.lock
	phpunit.xml.dist
```

After you installed the both packages `inpsyde/wordpress-dev` and `inpsyde/wp-tests-starter` like described above, they will
appear in your composer.json:

```
  "require-dev": {
    "inpsyde/wordpress-dev": "~4.4",
    "inpsyde/wp-tests-starter": "~1.0",
  }
```
The packages itself are installed inside the `vendor/` directory. Now that you have all dependencies, prepare your `phpunit.xml.dist`:

```xml
<phpunit
	bootstrap="tests/bootstrap.php"
	backupGlobals="false"
	colors="true"
	convertErrorsToExceptions="true"
	convertNoticesToExceptions="true"
	convertWarningsToExceptions="true"
	>
	<php>
		<const name="SomePlugin\Test\DB_USER" value="PLACEHOLDER" />
		<const name="SomePlugin\Test\DB_NAME" value="PLACEHOLDER" />
		<const name="SomePlugin\Test\DB_PASSWORD" value="PLACEHOLDER" />
		<const name="SomePlugin\Test\DB_HOST" value="localhost" />
		<const name="SomePlugin\Test\DB_CHARSET" value="utf8" />
		<const name="SomePlugin\Test\DB_COLLATE" value="" />
		<const name="SomePlugin\Test\DB_TABLE_PREFIX" value="some_plugin_" />
	</php>
	<testsuites>
		<testsuite name="WpIntegrationTests">
			<directory suffix="Test.php">./tests/WpIntegration</directory>
		</testsuite>
	</testsuites>
</phpunit>
```
This configures PHPUnit to do the following steps:

* defining the constants declared in the `<php></php>` section.
* loading the `test/bootstrap.php` file
* iterating recursively over the `tests/WpIntegration` directory looking for files named like `*Test.php`, treating
each file as a PHPUnit test case.

**Important!** Make sure you don't write your actual credentials into `phpunit.xml.dist`! This file acts as template and
should be placed under version control (e.g. git). That's what the `.dist` stands for. Instead, make a local copy of
`phpunit.xml.dist` and name it `phpunit.xml`. Make sure `phpunit.xml` is ignored by your VCS (e.g. add it to `.gitignore`
when git is your weapon of choice). Now write your database credentials into `phpunit.xml`.

Finally we write the required code to the `tests/bootstrap.php` to bootstrap everything.

```php
<?php // tests/bootstrap.php

namespace SomePlugin\Test;

use
	 WpTestsStarter\WpTestsStarter;

/**
 * require composers autoload file, if it exists
 * __DIR__ points to the tests/ directroy
 */
$base_dir = dirname( __DIR__ );

$autoload_file = "{$base_dir}/vendor/autoload.php";
if ( file_exists( $autoload_file ) ) {
	/**
	 * this will load all your dependencies including WP Tests Starter
	 * except the wordpress core as it does not support autoloading
	 */
	require_once $autoload_file;
}

/**
 * the path is fine for the default configuration of composer
 * you only have to adapt it, when you configured composer to use
 * custom install paths
 */
$starter = new WpTestsStarter( "{$base_dir}/vendor/inpsyde/wordpress-dev" );

// phpunit defined these constants for you
$starter->defineDbName( DB_NAME );
$starter->defineDbUser( DB_USER );
$starter->defineDbPassword( DB_PASSWORD );
$starter->setTablePrefix( DB_TABLE_PREFIX );

// this will finally create the wp-tests-config.php and include the wordpress core tests bootstrap
$starter->bootstrap();
```

That's all about configuration. Let's have a quick look on a sample test case in `tests/WpIntegration/SomePluginCodeTest.php`.

```php
<?php

namespace SomePlugin\Test\WpIntegration;

use
	WP_UnitTestCase;

class SomePluginCodeTest extends WP_UnitTestCase {

	public function test_it() {

		$this->assertTrue(
			class_exists( 'WP_Query' )
		);
	}
}
```
As you can see, your tests now running under a complete WordPress environment. So for example you can use the `WP_UnitTestCase`
class which performs a database rollback after each test.

## API reference

WP Tests starter provides the following methods to configure your tests.

```
WpTestsStarter WpTestsStarter::__construct( string $baseDir, [ WpTestsStarter\Common\SaltGeneratorInterface $saltGenerator = NULL ] )
```
`$baseDir` must specify the path to the directory of the WordPres developer package.
`$saltGenerator` can be an instance of `SaltGeneratorInterface`. Default is `WpTestsStarter\Common\SaltGenerator`.

---

```
void WpTestsStarter::bootstrap()
```
Defines missing mandatory constants, creates the file `wp-tests-config.php` and loads the WordPress core bootstrap.

---

```
bool WpTestsStarter::defineConst( string $fqn, mixed $value );
```
Defines an arbitrary constant `$fqn` (full qualified name) with the value `$value`.
Example `$starter->defineConst( 'SomePlugin\FOO', 'Hello World' );` Returns `FALSE` if the constant already exists,
otherwise `TRUE`.

---

```
void WpTestsStarter::defineAbspath( [ string $absPath = '' ] );
```
Defines the constant `\ABSPATH`. Default value is `WpTestsStarter::$baseDir . '/src/`, if no or empty value is given.

---

```
void WpTestsStarter::defineDbName( string $dbName );
```
Defines the constant `\DB_NAME`.

---

```
void WpTestsStarter::defineDbHost( [ string $dbHost = 'localhost' ] );
```
Defines the constant `\DB_HOST`.

---

```
void WpTestsStarter::defineDbUser( string $dbUser );
```
Defines the constant `\DB_USER`.

---

```
void WpTestsStarter::defineDbPassword( string $dbPassword );
```
Defines the constant `\DB_PASSWORD`.

---

```
void WpTestsStarter::defineDbCharset( [ string $dbCharset = 'utf8' ] );
```
Defines the constant `\DB_CHARSET`.

---

```
void WpTestsStarter::defineDbCollate( [ string $dbCollate = '' ] );
```
Defines the constant `\DB_COLLATE`.

---

```
void WpTestsStarter::defineWpDebug( [ bool $dbDebug = FALSE ] );
```
Defines the constant `\WP_DEBUG`.

---

```
void WpTestsStarter::defineSalts();
```
Create random values for each of the keys and defines the constants `\AUTH_KEY`, `\SECURE_AUTH_KEY`, `\LOGGED_IN_KEY`,
`\NONCE_KEY`, `\SECURE_AUTH_SALT`, `\LOGGED_IN_SALT` and `\NONCE_SALT`.

---

```
void WpTestsStarter::defineTestsDomain( [ string $domain = 'example.org' ] );
```
Defines the constant `\WP_TESTS_DOMAIN`.

---

```
void WpTestsStarter::defineTestsEmail( [ string $email = 'admin@example.org' ] );
```
Defines the constant `\WP_TESTS_EMAIL`.

---

```
void WpTestsStarter::defineTestsTitle( [ string $title = 'Test Blog' ] );
```
Defines the constant `\WP_TESTS_TITLE`.

---

```
void WpTestsStarter::definePhpBinary( [ string $binary = 'php' ] );
```
Defines the constant `\WP_PHP_BINARY`.

---

```
void WpTestsStarter::defineWpLang( [ string $lang = '' ] );
```
Defines the constant `\WPLANG`.

---

```
void WpTestsStarter::defineTestForceKnownBugs( bool $flag );
```
Defines the constant `\WP_TESTS_FORCE_KNOWN_BUGS`.

---

```
void WpTestsStarter::defineTestMultisite( bool $flag );
```
Defines the constant `\WP_TESTS_MULTISITE`.

---

```
void WpTestsStarter::defineWpPluginDir( string $dir );
```
Defines the constant `\WP_PLUGIN_DIR`.

---

```
void WpTestsStarter::setActivePlugin( string $plugin );
```
Activates a plugin for the test run. Provide a file path relative to `\WP_PLUGIN_DIR` e.g. `some_plugin/some_plugin.php`.
The function can be used one or more times to specify more than one acitve plugin.

Declares the global array `$GLOBALS[ 'wp_tests_options' ][ 'active_plugins' ]`.

---

```
void WpTestsStarter::setTablePrefix( [ string $tablePrefix = 'wptests_' ] );
```
Declares the global variable `$GLOBALS[ 'table_prefix' ]`.

---

```
void WpTestsStarter::setGlobal( string $var, mixed $value );
```
Declares or overrides the global variable `$GLOBALS[ $var ]` to the value `$value`.

---

```
void WpTestsStarter::createDummyConfigFile();
```
Writes all constants and globals, that was configured via this WpTestsStarter instance to the file
`"{WpTestsStarter::getConfigFile()}/wp-tests-config.php"`.

---

```
string WpTestsStarter::getConfigFile();
```
Returns `"{WpTestsStarter::$baseDir}/wp-tests-config.php"`.

---

```
array WpTestsStarter::getDefinedConstants();
```
Returns all constants defined with this instance as an array `[ name -> value ]`.


## How it works
This package writes dynamically the definition of the constants in the `wp-tests-config.php` file inside the
packages directory (typically `vendor/inpsyde/wordpress-dev`).

## Security notice
You should **not** use this package in productive environments but only on well controlled developing and testing
environments. The package generates PHP code which is in fact nothing else then `eval()`. It is
necessary as the `wp-tests-config.php` has to be placed inside the package root directory which is typically under
composer control.

## License
Good news, this plugin is free for everyone! Since it's released under [MIT-License](LICENSE), you can use it free of
charge on your personal or commercial website.

## Created by Inpsyde

Visit us at [inpsyde.com](http://inpsyde.com/).