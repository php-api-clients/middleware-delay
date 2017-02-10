<?php

namespace ApiClients\Tests\Middleware\Delay;

use ApiClients\Middleware\Delay\DelayMiddleware;
use ApiClients\Middleware\Delay\Options;
use ApiClients\Tools\TestUtilities\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\Factory;
use function Clue\React\Block\await;
use function React\Promise\resolve;

final class DelayMiddlewareTest extends TestCase
{
    public function testRequest()
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
        $loop->futureTick(function () use (&$preCalled, $middleware, $request, $options) {
            $middleware->pre($request->reveal(), $options)->then(function () use (&$preCalled) {
                $preCalled = true;
            });
        });

        self::assertFalse($preCalled);

        $start = microtime(true);

        $loop->run();

        $stop = microtime(true);

        self::assertNotSame($start + 3, $stop);
        self::assertTrue($start + 3 <= $stop, $start + 3 . ' vs ' . $stop);

        self::assertTrue($preCalled);
    }

    public function testRequestNoDelay()
    {
        $request = $this->prophesize(RequestInterface::class);

        $options = [];
        $middleware = new DelayMiddleware(Factory::create());
        $preCalled = false;
        $middleware->pre($request->reveal(), $options)->then(function () use (&$preCalled) {
            $preCalled = true;
        });

        self::assertTrue($preCalled);
    }
}
