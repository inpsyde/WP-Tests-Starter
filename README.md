# WP Tests Starter
Quickly set up an environment to test your plugin integration with the WordPress core testing framework.

## Usage
Define the required packages in your projects composer.json:

```
{
	"require-dev":{
		"inpsyde/wordpress-dev":"~4.2",
		"inpsyde/wp-tests-starter":"dev-master"
	}
}
```

Then run the install process:

```
$ composer install
```

In your phpunit testing bootstrap file use the starter like this:

```php
use WpTestsStarter\WpTestsStarter;

// The composer autoloader
require_once 'vendor/autoload.php';

// The full path to the wordpress package
$starter = new WpTestsStarter( '/path/to/your-project/vendor/inpsyde/wordpress-dev' );

// Minimum required settings
$starter->defineDbName( 'your-test-db' );
$starter->defineDbUser( 'your-test-user' );
$starter->defineDbPassword( 'your-test-user' );
$starter->setTablePrefix( 'your_table_prefix_' );

// optional config settings
$testee->defineDbHost( 'localhost' );
$testee->defineDbCharset( 'utf8' );
$testee->defineDbCollate( '' );
$testee->defineAbspath();
$testee->definePhpBinary( 'php' );
$testee->defineWpLang( '' );
$testee->defineWpDebug( TRUE );
$testee->defineTestsDomain( 'example.com' );
$testee->defineTestsEmail( 'admin@example.com' );
$testee->defineTestsTitle( 'Test site' );

// you can define own constants as well
$testee->defineConst( 'WP_TESTS_MULTISITE', TRUE );

// This will finally include the WordPress test bootstrap
$starter->bootstrap();
```

## How it works
This package writes dynamically the definition of the constants in the `wp-tests-config.php` file inside the
packages directory (typically `vendor/inpsyde/wordpress-dev`).