<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{
    const APP_ENV = 'dev';

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'db-dev';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'videgrenier';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'user';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = 'password';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    public static function getAppEnv()
    {
        return self::getEnv('APP_ENV', self::APP_ENV);
    }

    public static function getDbHost()
    {
        return self::getEnv('DB_HOST', self::DB_HOST);
    }

    public static function getDbName()
    {
        return self::getEnv('DB_NAME', self::DB_NAME);
    }

    public static function getDbUser()
    {
        return self::getEnv('DB_USER', self::DB_USER);
    }

    public static function getDbPassword()
    {
        return self::getEnv('DB_PASSWORD', self::DB_PASSWORD);
    }

    public static function showErrors()
    {
        return filter_var(
            self::getEnv('APP_SHOW_ERRORS', self::SHOW_ERRORS ? 'true' : 'false'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private static function getEnv($key, $default)
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }
}
