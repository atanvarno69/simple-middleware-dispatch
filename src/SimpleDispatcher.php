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

class SimpleDispatcher extends BaseDispatcher
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        MiddlewareInterface ...$middleware
    ) {
        parent::__construct($responseFactory, $streamFactory);
        $this->queue = $middleware;
    }

    public function append(MiddlewareInterface $middleware)
    {
        $this->queue[] = $middleware;
    }

    public function prepend(MiddlewareInterface $middleware)
    {
        array_unshift($this->queue, $middleware);
    }

    public function getNextMiddleware()
    {
        $return = array_shift($this->queue);
        return $return ?? false;
    }
}
