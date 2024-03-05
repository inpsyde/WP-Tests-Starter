# WP Tests Starter
Note: You're reading the documentation for the development branch towards version 2.0.0. You'll find the documentation
for version 1.0 at the branch [version-1](https://github.com/inpsyde/WP-Tests-Starter/tree/version-1)

Wp Tests starter is a library that assist you in setting up _integration tests_ for your plugin or library with WordPress
 core using the official [wordpress-develop repository](https://github.com/inpsyde/wordpress-dev). The main difference to
 unit tests is that you don't need (and typically don't want) to mock any WordPress function. Instead, you have a fully
 booted WordPress core in place with an actual connection to a database server.

So if you're mapping your objects to WordPress posts in a ORM-style way your integration test would look like this:

```php
public function testPersistBook(): void {

    $book = new Book('The Da Vinci Code', 'Dan Brown', '2003');
    $testee = new BookRepository($GLOBALS['wpdb']);
    $testee->persist($book); // maps book to WP_Post object and post meta

    self::assertGreaterThan(0, $book->id());

    $wpPost = get_post($book->id());

    self::assertSame('The Da Vinci Code', $wpPost->post_title);
    self::assertSame('2003', get_post_meta($book->id(), '_publishing_year', true));
    self::assertSame('Dan Brown', get_post_meta($book->id(), '_author', true));
}
```

No mocks required. Just WordPress working inside a PHPUnit test case.

## Installation

In order to use Wp Tests Starter you need: a PHP environment with Composer and a MySQL server with a _dedicated test database_. This database should be completely ephemeral, so do not use any database that contains important data. You'll also need the following four Composer packages installed as dev dependencies:

* `inpsyde/wp-tests-starter`
* `yoast/phpunit-polyfills`
* `wordpress/wordpress` taken from [wordpress-develop repository](https://github.com/inpsyde/wordpress-dev)
* `phpunit/phpunit`

As the last one is not available on packagist.org, you'll have to add the repo manually to your composer.json file by adding:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/WordPress/wordpress-develop"
    }
]
```

Now you can run

    composer require --dev inpsyde/wp-tests-starter yoast/phpunit-polyfills wordpress/wordpress phpunit/phpunit

Note that this will take a while as Composer will analyze the entire WordPress repository on GitHub. (Once a composer.lock is in place it will go faster on the next install run)

## Setup your tests

To set up your PHPUnit tests you need two files in place: `phpunit.xml.dist` and a `boostrap.php` which gets loaded by
PHPUnit before your actual tests are executed. The shown examples of these two files assume a directory structure of your
library like this:

    ├ src/
    |  └ MyModule.php
    ├ tests/
    |  ├integration/
    |  |  └ MyModuleTest.php
    |  └boostrap.php
    ├ vendor/
    ├ composer.json
    └ phpunit.xml.dist

The following example of the phpunit.xml.dist file tells PHPUnit where the test files resides and contains the database
credentials as an environment variable:

```xml
<phpunit
    bootstrap="tests/bootstrap.dist.php"
>
    <php>
        <env name="WPTS_DB_URL" value="mysql://user:password@host/db_name?table_prefix=wp_test_"/>
    </php>
    
    <testsuites>
        <testsuite name="integration">
            <directory suffix="Test.php">./tests/integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

The `tests/boostrap.php` finally loads Wp Tests Starter and WordPress:

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Inpsyde\WpTestsStarter\WpTestsStarter;

$projectDir = dirname(__DIR__);

require_once $projectDir . '/vendor/autoload.php';

$starter = new WpTestsStarter(
    $projectDir . '/vendor/wordpress/wordpress', // path to the WordPress library
    getenv('WPTS_DB_URL') // Databse credentials in URL format, set in phpunit.xml.dist
);

// Some configuration:
$starter
    // Install WP core as multisite
    ->testAsMultisite()
    // boostrap your plugin or module code
    ->addActivePlugin(static function()  {
        (new MyModule())->init();
    })
    // add filters early
    ->addFilter('my_app.modules', static function(array $modules): array {
        // whatever, it's just an example
        return  $modules;
    })
    //finally load WordPress
    ->bootstrap();
```

These files just show a short way to run integration tests with WP Tests Starter. You have several other configuration
options available through the methods of the `WpTestsStarter` object.

## Run PHPUnit

With this configuration in place you can run PHPUnit to execute all test classes in `tests/integration` with

    vendor/bin/phpunit

On every run, WP Starter will write the configuration to `vendor/wordpress/wordpress/wp-config.php` and load the WordPress
internal bootstrap script which ensures installation of the database tables for example.

## Configuration

### DB Url

In order to not have to maintain several environment variables you can pass all databse credentials and options via a
single parameter like this:

    mysql://user:password@localhost:3306/test_db?table_prefix=wp_tests_&charset=utf8mb4&collation=utf8_general_ci

This URL can be passed to `WpTestsStarter` either by constructor parameter or by `useDbUrl()` method:

```php
<?php
//either
$starter = new WpTestsStarter($baseDir, $dbUrl);
// or
$starter->useDbUrl($dbUrl);
```

The URL example above would turn into the following WordPress constants and globals:

```php
<?php
define('DB_HOST', 'localhost:3306');
define('DB_USER', 'user');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'test_db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8_general_ci');
$GLOBALS['table_prefix'] = 'wp_tests_'
```

Basically all the values of this URL are optional but keep in mind that you want to have a complete configuration
for WordPress. Alternatively you can use explicit values:

```php
$starter
    ->useDbHost('localhost')
    ->useDbUser('user');
    // and so on
```

## License
This repository is a free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](./LICENSE) for complete license.


[Back to top](#wp-tests-starter)
