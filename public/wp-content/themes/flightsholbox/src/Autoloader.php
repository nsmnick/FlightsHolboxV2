<?php

namespace Theme;

class Autoloader
{
    /**
     * Namespace prefix.
     *
     * @var string
     */
    public static string $prefix = 'Theme\\';

    /**
     * Base directory of class source files.
     *
     * @var string
     */
    public static string $base_dir = __DIR__ . DIRECTORY_SEPARATOR;

    /**
     * Register PSR-4 style autoloader for theme.
     *
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $len = strlen(self::$prefix);
            if (strncmp(self::$prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            $file = self::$base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
