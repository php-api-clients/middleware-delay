<?php declare(strict_types=1);

namespace ApiClients\Middleware\Delay;

use ApiClients\Foundation\Middleware\Annotation\Second;
use ApiClients\Foundation\Middleware\ErrorTrait;
use ApiClients\Foundation\Middleware\MiddlewareInterface;
use ApiClients\Foundation\Middleware\PostTrait;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\LoopInterface;
use React\Promise\CancellablePromiseInterface;
use function React\Promise\resolve;
use function WyriHaximus\React\timedPromise;

final class DelayMiddleware implements MiddlewareInterface
{
    use PostTrait;
    use ErrorTrait;

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
     *
     * @Second()
     */
    public function pre(
        RequestInterface $request,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
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
}
