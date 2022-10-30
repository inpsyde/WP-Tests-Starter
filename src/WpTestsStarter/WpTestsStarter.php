<?php

declare(strict_types=1);

namespace WpTestsStarter;

use WpTestsStarter\Helper\SaltGenerator;

class WpTestsStarter
{
    private string $baseDir;

    private SaltGenerator $saltGenerator;

    /**
     * @type string[]
     */
    private array $definedConstants = [];

    /**
     * @param string $baseDir Absolute path to the wordpress-develop repository
     */
    public function __construct(string $baseDir, ?SaltGenerator $saltGenerator = null)
    {
        $this->baseDir = rtrim($baseDir, '\\/');
        $this->saltGenerator = $saltGenerator ?? new SaltGenerator();
    }

    /**
     * Loading the WordPress testing bootstrap
     */
    public function bootstrap()
    {
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

        $this->createDummyConfigFile();

        $wpBoostrapFile = $this->baseDir . '/tests/phpunit/includes/bootstrap.php';
        require_once $wpBoostrapFile;
    }

    /**
     * @param mixed $value
     */
    public function defineConst(string $const, $value): bool
    {
        if (defined($const)) {

            return false;
        }

        $this->definedConstants[$const] = $value;

        return define($const, $value);
    }

    public function defineAbspath(?string $abspath = null): void
    {
        if (empty($abspath)) {
            $abspath = $this->baseDir . '/src/';
        }
        $this->defineConst('ABSPATH', $abspath);
    }

    public function defineDbName(string $dbName): void
    {
        $this->defineConst('DB_NAME', $dbName);
    }

    public function defineDbHost(string $dbHost = 'localhost'): void
    {
        $this->defineConst('DB_HOST', $dbHost);
    }

    public function defineDbUser(string $dbUser): void
    {
        $this->defineConst('DB_USER', $dbUser);
    }

    public function defineDbPassword(string $dbPassword): void
    {
        $this->defineConst('DB_PASSWORD', $dbPassword);
    }

    public function defineDbCharset(string $dbCharset = 'utf8'): void
    {
        $this->defineConst('DB_CHARSET', $dbCharset);
    }

    public function defineDbCollate(string $dbCollate = ''): void
    {
        $this->defineConst('DB_COLLATE', $dbCollate);
    }

    public function defineWpDebug(bool $wpDebug = false): void
    {
        $this->defineConst('WP_DEBUG', $wpDebug);
    }

    public function defineSalts()
    {
        $saltConstants = [
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT',
            'AUTH_KEY',
        ];

        foreach ($saltConstants as $constant) {
            $this->defineConst(
                $constant,
                $this->saltGenerator->generateSalt()
            );
        }
    }

    public function defineTestsDomain(string $domain = 'example.org'): void
    {
        $this->defineConst('WP_TESTS_DOMAIN', $domain);
    }

    public function defineTestsEmail(string $email = 'admin@example.org'): void
    {
        $this->defineConst('WP_TESTS_EMAIL', $email);
    }

    public function defineTestsTitle(string $title = 'Test Blog'): void
    {
        $this->defineConst('WP_TESTS_TITLE', $title);
    }

    public function definePhpBinary(string $binary = 'php'): void
    {
        $this->defineConst('WP_PHP_BINARY', $binary);
    }

    public function defineWpLang(string $lang = ''): void
    {
        $this->defineConst('WPLANG', $lang);
    }

    public function defineTestForceKnownBugs(bool $flag): void
    {
        $this->defineConst('WP_TESTS_FORCE_KNOWN_BUGS', $flag);
    }

    public function defineTestMultisite(bool $flag): void
    {
        $this->defineConst('WP_TESTS_MULTISITE', $flag);
    }

    public function defineWpPluginDir(string $dir): void
    {
        $dir = rtrim($dir, '\\/');
        $this->defineConst('WP_PLUGIN_DIR', $dir);
    }

    /**
     * @param string $plugin a plugin file relative to WP's plugin directory like 'directory/plugin-file.php'
     */
    public function setActivePlugin(string $plugin): void
    {
        if (! isset($GLOBALS['wp_tests_options'])) {
            $GLOBALS['wp_tests_options'] = [];
        }

        if (! isset($GLOBALS['wp_tests_options']['active_plugins'])) {
            $GLOBALS['wp_tests_options']['active_plugins'] = [];
        }

        if (in_array($plugin, $GLOBALS['wp_tests_options']['active_plugins'])) {
            return;
        }

        $GLOBALS['wp_tests_options']['active_plugins'][] = $plugin;
    }

    public function setTablePrefix(string $prefix = 'wptests_'): void
    {
        $var = 'table_prefix';
        $this->setGlobal($var, $prefix);
    }

    /**
     * @param mixed $value
     */
    public function setGlobal(string $var, $value): void
    {
        $GLOBALS[$var] = $value;
    }

    public function createDummyConfigFile()
    {
        $configFile = $this->getConfigFile();
        if (! file_exists($configFile)) {
            touch($configFile);
        }

        /**
         * We have to persist all dynamic configuration (constants and globals) in a wp-config.php
         * as the WordPress internal boostrap process runs a sub process for the installation (setup DB tables)
         */
        $constantsDefinition = $this->getDefinedConstantsCode();
        $content = <<<PHP
<?php
global \$table_prefix;
\$GLOBALS[ 'table_prefix' ] = "{$GLOBALS['table_prefix']}";
{$constantsDefinition}
PHP;
        file_put_contents($configFile, $content, LOCK_EX);
    }

    public function getConfigFile(): string
    {
        return $this->baseDir . '/wp-tests-config.php';
    }

    /**
     * @return string[]
     */
    public function getDefinedConstants(): array
    {
        return $this->definedConstants;
    }

    public function getDefinedConstantsCode(): string
    {
        $code = '';
        foreach ($this->definedConstants as $constant => $value) {
            $constant = $this->escapePhpString($constant);
            $value = $this->escapePhpString($value);
            $code .= "if ( ! defined( '{$constant}' ) )\n";
            $code .= "\tdefine( '{$constant}', '{$value}' );\n";
        }

        return $code;
    }

    public function escapePhpString($value): string
    {
        $value = str_replace(
            ['<?php', '<?', '?>'],
            '',
            $value
        );
        $value = addcslashes($value, "'\\");

        return $value;
    }
}
