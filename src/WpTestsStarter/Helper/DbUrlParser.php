<?php

declare(strict_types=1);

namespace WpTestsStarter\Helper;

use WpTestsStarter\Exception\RuntimeException;

class DbUrlParser
{

    /**
     * Parses a URL like mysql://user:password@host/database?table_prefix=wp_&charset=utf8mb4
     * all parts are optional
     *
     * @return array{
     *      host: ?string,
     *      user: ?string,
     *      password: ?string,
     *      db: ?string,
     *      table_prefix: ?string,
     *      charset: ?string
     * }
     */
    public function parse(string $dbUrl): array
    {
        $credentials = [
            'host' => null,
            'user' => null,
            'password' => null,
            'db' => null,
            'table_prefix' => null,
            'charset' => null,
        ];

        $parts = parse_url($dbUrl);
        if (! is_array($parts)) {
            throw new RuntimeException("Please provide a valid dbUrl");
        }

        if (array_key_exists('scheme', $parts) && $parts['scheme'] !== 'mysql') {
            throw new RuntimeException("Currently only 'mysql' databases are supported");
        }

        array_key_exists('host', $parts) and $credentials['host'] = $parts['host'];
        array_key_exists('user', $parts) and $credentials['user'] = $parts['user'];
        array_key_exists('pass', $parts) and $credentials['password'] = $parts['pass'];
        array_key_exists('path', $parts) and $credentials['db'] = trim($parts['path'], '/');

        if (! array_key_exists('query', $parts) || empty($parts['query'])) {
            return $credentials;
        }

        $query = [];
        parse_str($parts['query'], $query);

        array_key_exists('table_prefix', $query) and $credentials['table_prefix'] = $query['table_prefix'];
        array_key_exists('charset', $query) and $credentials['charset'] = $query['charset'];

        return $credentials;
    }
}
