<?php

declare(strict_types=1);

namespace Inpsyde\WpTestsStarter;

use Inpsyde\WpTestsStarter\Helper\DbUrlParser;
use Inpsyde\WpTestsStarter\Helper\SaltGenerator;

class WpTestsStarter
{
    private string $baseDir;

    private SaltGenerator $saltGenerator;

    private DbUrlParser $dbUrlParser;

    /**
     * @var callable[]
     */
    private static $livePlugins = [];

    /**
     * @var callable[]
     */
    private array $globalsFactories = [];

    private array $constants = [];

    /**
     * @param string $baseDir Absolute path to the wordpress-develop repository
     */
    public function __construct(
        string $baseDir,
        ?string $dbUrl = '',
        ?SaltGenerator $saltGenerator = null,
        ?DbUrlParser $dbUrlParser = null
    ) {
        $this->baseDir = rtrim($baseDir, '\\/');
        $this->saltGenerator = $saltGenerator ?? new SaltGenerator();
        $this->dbUrlParser = $dbUrlParser ?? new DbUrlParser();

        // set some common defaults
        $this->useDbHost('localhost')
            ->useSiteDomain('example.tld')
            ->useEmail('admin@example.tld')
            ->useSiteTitle('Wp Tests Starter')
            ->usePhpBinary('/usr/bin/php')
            ->useTablePrefix('wp_tests_')
            ->useAbsPath($this->baseDir . '/src/')
            ->generateSalts();

        $dbUrl and $this->useDbUrl($dbUrl);
    }

    /**
     * Loading the WordPress testing bootstrap
     */
    public function bootstrap()
    {
        $this->defineConstants();
        $this->declareGlobals();
        $this->writeConfigFile();
        $this->installLivePlugin();

        $wpBoostrapFile = $this->baseDir . '/tests/phpunit/includes/bootstrap.php';
        require_once $wpBoostrapFile;
    }

    public function useConst(string $const, $value): self
    {
        $this->constants[$const] = $value;

        return $this;
    }

    public function useGlobalVar(string $var, $value): self
    {
        $this->globalsFactories[] = static function () use ($var, $value): void {
            $GLOBALS[$var] = $value;
        };

        return $this;
    }

    public function useDbUrl(string $dbUrl): self
    {
        $credentials = $this->dbUrlParser->parse($dbUrl);

        $credentials['host'] and $this->useDbHost($credentials['host']);
        $credentials['user'] and $this->useDbUser($credentials['user']);
        $credentials['password'] and $this->useDbPassword($credentials['password']);
        $credentials['db'] and $this->useDbName($credentials['db']);
        $credentials['table_prefix'] and $this->useTablePrefix($credentials['table_prefix']);
        $credentials['charset'] and $this->useDbCharset($credentials['charset']);
        $credentials['collation'] and $this->useDbCollation($credentials['collation']);

        return $this;
    }

    public function useAbsPath(?string $abspath = null): self
    {
        return $this->useConst('ABSPATH', $abspath);
    }

    public function useDbName(string $dbName): self
    {
        return $this->useConst('DB_NAME', $dbName);
    }

    public function useDbHost(string $dbHost): self
    {
        return $this->useConst('DB_HOST', $dbHost);
    }

    public function useDbUser(string $dbUser): self
    {
        return $this->useConst('DB_USER', $dbUser);
    }

    public function useDbPassword(string $dbPassword): self
    {
        return $this->useConst('DB_PASSWORD', $dbPassword);
    }

    public function useDbCharset(string $dbCharset): self
    {
        return $this->useConst('DB_CHARSET', $dbCharset);
    }

    public function useDbCollation(string $dbCollation): self
    {
        return $this->useConst('DB_COLLATE', $dbCollation);
    }

    public function useDebugMode(bool $wpDebug): self
    {
        return $this->useConst('WP_DEBUG', $wpDebug);
    }

    public function useSiteDomain(string $domain): self
    {
        return $this->useConst('WP_TESTS_DOMAIN', $domain);
    }

    public function useEmail(string $email): self
    {
        return $this->useConst('WP_TESTS_EMAIL', $email);
    }

    public function useSiteTitle(string $title): self
    {
        return $this->useConst('WP_TESTS_TITLE', $title);
    }

    public function usePhpBinary(string $binary): self
    {
        return $this->useConst('WP_PHP_BINARY', $binary);
    }

    public function testAsMultisite(bool $isMultisite): self
    {
        return $this->useConst('WP_TESTS_MULTISITE', $isMultisite);
    }

    public function useWpPluginDir(string $dir): self
    {
        $dir = rtrim($dir, '\\/');

        return $this->useConst('WP_PLUGIN_DIR', $dir);
    }

    /**
     * @param string $plugin a plugin file relative to WP's plugin directory like 'directory/plugin-file.php'
     */
    public function addActivePlugin(string $plugin): self
    {
        $this->globalsFactories[] = static function () use ($plugin): void {
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
        };

        return $this;
    }

    public function addLivePlugin(callable $plugin): self
    {
        self::$livePlugins[] = $plugin;

        return $this;
    }

    public function useTablePrefix(string $prefix): self
    {
        return $this->useGlobalVar('table_prefix', $prefix);
    }

    public static function runLivePlugins(): void
    {
        foreach(self::$livePlugins as $livePlugin) {
            $livePlugin();
        }
    }

    private function installLivePlugin(): void
    {
        if(!self::$livePlugins) {
            return;
        }

        $pluginCode = <<<'PHP'
<?php

declare(strict_types=1);

/**
 * Plugin Name: Wp Tests Starter live plugin
 */
namespace Inpsyde\WpTestsStarter;

if (!class_exists(\Inpsyde\WpTestsStarter\WpTestsStarter::class)) {
    return;
}

add_action(
    'muplugins_loaded',
    [\Inpsyde\WpTestsStarter\WpTestsStarter::class, 'runLivePlugins']
);
PHP;

        $muPluginDir = $this->muPluginDir();
        if(!is_dir($muPluginDir)) {
            mkdir($muPluginDir, 0755, true);
        }

        $pluginFile = $muPluginDir . '/wp-tests-starter-live-plugin.php';
        file_put_contents(
            $pluginFile,
            $pluginCode
        );
    }

    private function defineConstants(): void
    {
        foreach ($this->constants as $constant => $value) {
            if (defined($constant)) {
                continue;
            }

            define($constant, $value);
        }
    }

    private function declareGlobals(): void
    {
        foreach ($this->globalsFactories as $factory) {
            $factory();
        }
    }

    private function generateSalts()
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
            $this->useConst(
                $constant,
                $this->saltGenerator->generateSalt()
            );
        }
    }

    private function muPluginDir(): ?string
    {
        if(defined('WPMU_PLUGIN_DIR')) {
            return (string) WPMU_PLUGIN_DIR;
        }

        if(array_key_exists('WPMU_PLUGIN_DIR', $this->constants)) {
            return (string) $this->constants['WPMU_PLUGIN_DIR'];
        }

        if(defined('WP_CONTENT_DIR')) {
            return WP_CONTENT_DIR . '/mu-plugins';
        }

        if(array_key_exists('WP_CONTENT_DIR', $this->constants)) {
            return $this->constants['WP_CONTENT_DIR'] . '/mu-plugins';
        }

        return $this->baseDir . '/src/wp-content/mu-plugins';
    }

    private function writeConfigFile()
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

    private function getConfigFile(): string
    {
        return $this->baseDir . '/wp-tests-config.php';
    }

    private function getDefinedConstantsCode(): string
    {
        $code = '';
        foreach ($this->constants as $constant => $value) {
            $constant = $this->escapePhpString($constant);
            $value = $this->escapePhpString($value);
            $code .= "if ( ! defined( '{$constant}' ) )\n";
            $code .= "\tdefine( '{$constant}', '{$value}' );\n";
        }

        return $code;
    }

    private function escapePhpString($value): string
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
