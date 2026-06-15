<?php

declare(strict_types=1);

error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

if (session_status() === PHP_SESSION_NONE) {
    session_id('phpunit');
    session_start();
}
