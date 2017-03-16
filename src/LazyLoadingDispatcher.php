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
use Psr\Container\ContainerInterface;

class LazyLoadingDispatcher extends BaseDispatcher
{
    private $serviceLocator;

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

    public function append(string $middleware)
    {
        $this->queue[] = $middleware;
    }

    public function prepend(string $middleware)
    {
        array_unshift($this->queue, $middleware);
    }

    public function getNextMiddleware()
    {
        $id = array_shift($this->queue);
        $return = $this->serviceLocator->get($id);
        return ($return instanceof MiddlewareInterface) ? $return : false;
    }
}
