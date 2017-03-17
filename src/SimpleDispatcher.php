<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch;

/** PSR-15 use block. */
use Interop\Http\ServerMiddleware\MiddlewareInterface;

/** PSR-17 use block. */
use Interop\Http\Factory\{
    ResponseFactoryInterface, StreamFactoryInterface
};

/**
 * Atanvarno\Middleware\Dispatch\SimpleDispatcher
 *
 * Simple `Dispatcher` implementation that dispatches a queue of middleware.
 *
 * @api
 */
class SimpleDispatcher extends BaseDispatcher
{
    /**
     * Builds a `SimpleDispatcher` instance.
     *
     * Accepts PSR-17 factories and a queue of middleware.
     *
     * @param ResponseFactoryInterface $responseFactory PSR-17 response factory.
     * @param StreamFactoryInterface   $streamFactory   PSR-17 stream factory.
     * @param MiddlewareInterface[]    ...$middleware   Middleware queue.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        MiddlewareInterface ...$middleware
    ) {
        parent::__construct($responseFactory, $streamFactory);
        $this->queue = $middleware;
    }

    /**
     * Appends a middleware to the queue.
     *
     * @param MiddlewareInterface $middleware Middleware to append.
     *
     * @return $this Fluent interface.
     */
    public function append(MiddlewareInterface $middleware): Dispatcher
    {
        $this->queue[] = $middleware;
        return $this;
    }

    /**
     * Prepends a middleware to the queue.
     *
     * @param MiddlewareInterface $middleware Middleware to prepend.
     *
     * @return $this Fluent interface.
     */
    public function prepend(MiddlewareInterface $middleware): Dispatcher
    {
        array_unshift($this->queue, $middleware);
        return $this;
    }

    /** @inheritdoc */
    public function getNextMiddleware()
    {
        return array_shift($this->queue);
    }
}
