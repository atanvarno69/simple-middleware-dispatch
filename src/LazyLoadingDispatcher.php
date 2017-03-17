<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** PSR-15 use block. */
use Interop\Http\ServerMiddleware\MiddlewareInterface;

/** PSR-17 use block. */
use Interop\Http\Factory\{
    ResponseFactoryInterface, StreamFactoryInterface
};

/**
 * Atanvarno\Middleware\Dispatch\LazyLoadingDispatcher
 *
 * `Dispatcher` implementation that lazy loads middleware from a container.
 *
 * @api
 */
class LazyLoadingDispatcher extends BaseDispatcher
{
    /**
     * @internal Class property.
     *
     * @var ContainerInterface $serviceLocator PSR-11 container.
     */
    private $serviceLocator;

    /**
     * Builds a `LazyLoadingDispatcher` instance.
     *
     * Accepts PSR-17 factories, a PSR-11 container and a queue of middleware,
     * given as container keys.
     *
     * @param ResponseFactoryInterface $responseFactory PSR-17 response factory.
     * @param StreamFactoryInterface   $streamFactory   PSR-17 stream factory.
     * @param ContainerInterface       $serviceLocator  PSR-11 container.
     * @param string[]                 ...$middleware   Middleware queue.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ContainerInterface $serviceLocator,
        string ...$middleware
    ) {
        parent::__construct($responseFactory, $streamFactory);
        $this->serviceLocator = $serviceLocator;
        $this->queue = $middleware;
    }

    /**
     * Appends a middleware to the queue.
     *
     * Accepts a container key.
     *
     * @param string $middleware Middleware to append.
     *
     * @return $this Fluent interface.
     */
    public function append(string $middleware): Dispatcher
    {
        $this->queue[] = $middleware;
        return $this;
    }

    /**
     * Prepends a middleware to the queue.
     *
     * Accepts a container key.
     *
     * @param string $middleware Middleware to prepend.
     *
     * @return $this Fluent interface.
     */
    public function prepend(string $middleware): Dispatcher
    {
        array_unshift($this->queue, $middleware);
        return $this;
    }

    /** @inheritdoc */
    public function getNextMiddleware()
    {
        $id = array_shift($this->queue);
        $return = $this->serviceLocator->get($id);
        return ($return instanceof MiddlewareInterface) ? $return : null;
    }
}
