# WP Tests Starter
Note: You're reading the documentation for the development branch towards version 2.0.0. You'll find the documentation
for version 1.0 at the branch [version-1](https://github.com/inpsyde/WP-Tests-Starter/tree/version-1)

Wp Tests starter is a library that assist you in setting up _integration tests_ of your plugin or library with WordPress
 core using the official [wordpress-develop repository](https://github.com/inpsyde/wordpress-dev). The main difference to
 unit tests is that you don't need (and typically don't want) to mock any WordPress function. Instead, you have a fully
 booted WordPress core in place with an actual connection to a database server.

So if you're mapping your objects to WordPress posts in a ORM-style way your integration test would look like this:

```php
public function testPersistBook(): void {

    $book = new Book('The Da Vinci Code', 'Dan Brown', '2003');
    $testee = new BookRepository($GLOBALS['wpdb']);
    $testee->persist($book);

    self::assertGreaterThan(0, $book->id());

    $wpPost = get_post($book->id());

    self::assertSame('The Da Vinci Code', $wpPost->post_title);
}
```

No mocks required. Just WordPress working inside a PHPUnit test case.

## Setup

In order to use Wp Tests Starter you need: a PHP environment with Composer and a MySQL server with a dedicated test database. You'll need the following three Composer packages installed as dev dependencies:

* `inpsyde/wp-tests-starter`
* `yoast/phpunit-polyfills`
* `wordpress/wordpress` taken from [wordpress-develop repository](https://github.com/inpsyde/wordpress-dev)

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

    composer require --dev inpsyde/wp-tests-starter yoast/phpunit-polyfills wordpress/wordpress

Note that this will take a while as Composer will analyze the entire WordPress repository on GitHub. (Once a composer.lock is in place it will go faster on the next install run)

## Configuration

// Todo

## License
Good news, this plugin is free for everyone! Since it's released under this [License](LICENSE), you can use it free of
charge on your personal or commercial website.

## Created by Inpsyde

Visit us at [inpsyde.com](http://inpsyde.com/).

[Back to top](#wp-tests-starter)
