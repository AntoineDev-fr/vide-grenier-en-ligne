<?php

declare(strict_types=1);

namespace App\Controllers {

    class ProtectedArea extends \Core\Controller
    {
        public static bool $called = false;

        public function indexAction(): void
        {
            self::$called = true;
        }
    }
}

namespace Tests\Feature {

    use App\Controllers\ProtectedArea;
    use Core\Router;
    use Tests\TestCase;

    class RouteProtectionTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();

            ProtectedArea::$called = false;
        }

        public function testPrivateRouteIsDeniedWithoutSession(): void
        {
            $router = $this->makeRouter();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('You must be logged in');

            $router->dispatch('protected-area');
        }

        public function testPrivateRouteIsAllowedWithValidSession(): void
        {
            $_SESSION['user']['id'] = 42;

            $router = $this->makeRouter();
            $router->dispatch('protected-area');

            $this->assertTrue(ProtectedArea::$called);
        }

        private function makeRouter(): Router
        {
            $router = new Router();
            $router->add('protected-area', [
                'controller' => 'ProtectedArea',
                'action' => 'index',
                'private' => true
            ]);

            return $router;
        }
    }
}
