<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2018 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch;

/** SPL use block. */
use RuntimeException;

/** PSR-7 use block. */
use Psr\Http\Message\{
    ResponseInterface as Response, ServerRequestInterface as Request
};

/** PSR-15 use block. */
use Psr\Http\Server\{
    MiddlewareInterface as Middleware, RequestHandlerInterface as Handler
};

/**
 * Atanvarno\Middleware\Dispatch\SimpleDispatcher
 *
 * @api
 */
class SimpleDispatcher implements Handler, Middleware
{
    /**
     * @internal Class property.
     *
     * @var Handler $finalHandler Prototype response providing handler.
     */
    private $finalHandler;

    /**
     * @internal Class property.
     *
     * @var Middleware[] $queue Middleware queued for dispatch.
     */
    private $queue;

    /**
     * Builds a `SimpleDispatcher` instance.
     *
     * @param Middleware[] ...$middleware Middleware to queue for dispatch.
     */
    public function __construct(Middleware ...$middleware)
    {
        $this->queue = $middleware;
    }

    /**
     * Handles a request and produces a response.
     *
     * Response is produced by delegating to the middleware in the queue and
     * finally delegating to the final handler to supply a prototype response
     * when the queue is empty.
     *
     * NOTE: This method is for the use of middleware; you should not call it
     * directly as there will be no final handler to supply a prototype
     * response. Unless a middleware in your queue provides a response itself,
     * this method will throw an exception. You should instead call `process()`,
     * with the request and a response proving final handler to dispatch your
     * middleware queue.
     *
     * @param Request $request Request to handle.
     *
     * @throws RuntimeException No final handler available. You should
     *     provide one via the `process()` method rather than call this method
     *     directly.
     *
     * @return Response Produced response.
     */
    public function handle(Request $request): Response
    {
        if (empty($this->queue)) {
            if (!isset($this->finalHandler)) {
                throw new RuntimeException('No final handler available');
            }
            return $this->finalHandler->handle($request);
        }
        $middleware = array_shift($this->queue);
        return $middleware->process($request, $this);
    }

    /**
     * Processes an incoming server request and returns a response.
     *
     * Accepts a request, which will be passed through the queued middleware,
     * and a prototype response producing handler, which will be called if no
     * queued middleware produces a response before the end of queue is reached.
     *
     * @param Request $request Request to process.
     * @param Handler $handler Prototype response producing handler.
     *
     * @return Response
     */
    public function process(Request $request, Handler $handler): Response
    {
        $this->finalHandler = $handler;
        return $this->handle($request);
    }
}
