<?php declare(strict_types=1);

namespace ApiClients\Middleware\Delay;

use ApiClients\Foundation\Middleware\MiddlewareInterface;
use ApiClients\Foundation\Middleware\PostTrait;
use ApiClients\Foundation\Middleware\Priority;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\LoopInterface;
use React\Promise\CancellablePromiseInterface;
use function React\Promise\resolve;
use function WyriHaximus\React\timedPromise;

final class DelayMiddleware implements MiddlewareInterface
{
    use PostTrait;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * DelayMiddleware constructor.
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return CancellablePromiseInterface
     */
    public function pre(RequestInterface $request, array $options = []): CancellablePromiseInterface
    {
        if (!isset($options[self::class][Options::DELAY])) {
            return resolve($request);
        }

        return timedPromise(
            $this->loop,
            $options[self::class][Options::DELAY],
            $request
        )->then(function (RequestInterface $request) {
            return resolve($request);
        });
    }

    /**
     * @return int
     */
    public function priority(): int
    {
        return Priority::FIRST - 1;
    }
}
