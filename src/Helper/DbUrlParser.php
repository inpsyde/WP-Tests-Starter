<?php

declare(strict_types=1);

namespace Inpsyde\WpTestsStarter\Helper;

use Inpsyde\WpTestsStarter\Exception\RuntimeException;

class DbUrlParser
{

    /**
     * Parses a URL like mysql://user:password@host/database:3306?table_prefix=wp_&charset=utf8mb4&collation=utf8_general_ci
     * all parts are optional
     *
     * @return array{
     *      host: ?string,
     *      user: ?string,
     *      password: ?string,
     *      db: ?string,
     *      table_prefix: ?string,
     *      charset: ?string,
     *      collation: ?string
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
            'collation' => null,
        ];

        $parts = parse_url($dbUrl);
        if (! is_array($parts)) {
            throw new RuntimeException("Please provide a valid dbUrl");
        }

        if (array_key_exists('scheme', $parts) && $parts['scheme'] !== 'mysql') {
            throw new RuntimeException("Currently only 'mysql' databases are supported");
        }

        if (array_key_exists('host', $parts)) {
            $credentials['host'] = $parts['host'];
            if (array_key_exists('port', $parts)) {
                $credentials['host'] .= ':' . $parts['port'];
            }
        }

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
        array_key_exists('collation', $query) and $credentials['collation'] = $query['collation'];

        return $credentials;
    }
}
