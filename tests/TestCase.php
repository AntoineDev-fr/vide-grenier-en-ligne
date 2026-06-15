<?php

declare(strict_types=1);

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_COOKIE = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_SERVER = ['REQUEST_METHOD' => 'GET'];
    }
}
