<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Tests\Routing;

use Railt\Container\Container;
use Railt\Routing\Contracts\RouterInterface;
use Railt\Routing\Router;
use Railt\Tests\AbstractTestCase;

/**
 * Class RouterTestCase
 * @package Railt\Tests\Routing
 */
class RouterTestCase extends AbstractTestCase
{
    /**
     * @return RouterInterface
     */
    private function mock(): RouterInterface
    {
        return new Router(new Container());
    }

    /**
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testRouterStrictMatches(): void
    {
        $router = $this->mock();
        $router->any('some', 'Action');

        $this->assertTrue($router->has('some'));
        $this->assertFalse($router->has('before.some'));
        $this->assertFalse($router->has('some.after'));
        $this->assertFalse($router->has('ssome'));
    }

    /**
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testRouterResolveMatched(): void
    {
        $router = $this->mock();
        $router->any('some', 'Action');
        $router->query('some', 'Some');

        $this->assertCount(2, $router->get('some'));
        $this->assertCount(0, $router->get('before.some'));
        $this->assertCount(0, $router->get('some.after'));
        $this->assertCount(0, $router->get('somea'));
        $this->assertFalse($router->has('some.after'));
    }
}