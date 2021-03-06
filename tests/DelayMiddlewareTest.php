<?php declare(strict_types=1);

namespace ApiClients\Tests\Middleware\Delay;

use ApiClients\Middleware\Delay\DelayMiddleware;
use ApiClients\Middleware\Delay\Options;
use ApiClients\Tools\TestUtilities\TestCase;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\Factory;

/**
 * @internal
 */
final class DelayMiddlewareTest extends TestCase
{
    public function testRequest(): void
    {
        $loop = Factory::create();

        $request = $this->prophesize(RequestInterface::class);

        $options = [
            DelayMiddleware::class => [
                Options::DELAY => 3,
            ],
        ];
        $middleware = new DelayMiddleware($loop);
        $preCalled = false;
        $loop->futureTick(function () use (&$preCalled, $middleware, $request, $options): void {
            $middleware->pre($request->reveal(), 'abc', $options)->then(function () use (&$preCalled): void {
                $preCalled = true;
            });
        });

        self::assertFalse($preCalled);

        $start = \microtime(true);

        $loop->run();

        $stop = \microtime(true);

        self::assertNotSame($start + 3, $stop);
        self::assertTrue($start + 3 <= $stop, $start + 3 . ' vs ' . $stop);

        self::assertTrue($preCalled);
    }

    public function testRequestNoDelay(): void
    {
        $request = $this->prophesize(RequestInterface::class);

        $options = [];
        $middleware = new DelayMiddleware(Factory::create());
        $preCalled = false;
        $middleware->pre($request->reveal(), 'abc', $options)->then(function () use (&$preCalled): void {
            $preCalled = true;
        });

        self::assertTrue($preCalled);
    }
}
