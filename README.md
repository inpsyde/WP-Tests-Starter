# WP Tests Starter
Work in progress …

## Usage

```php
use WpTestsStarter\WpTestsStarter;

$starter = new WpTestsStarter( '/path/to/your-project/vendor/inpsyde/wordpress-dev' );

// Minimum required settings
$starter->defineDbName( 'your-test-db' );
$starter->defineDbUser( 'your-test-user' );
$starter->defineDbPassword( 'your-test-user' );
$starter->setTablePrefix( 'your_table_prefix_' );

$starter->bootstrap();
```