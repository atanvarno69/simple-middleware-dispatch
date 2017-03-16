<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch;

/** PSR-7 use block. */
use Psr\Http\Message\{
    ResponseInterface, ServerRequestInterface
};

/** PSR-15 use block. */
use Interop\Http\ServerMiddleware\{
    DelegateInterface, MiddlewareInterface
};

class Delegate implements DelegateInterface
{
    private $arguments, $default, $dispatcher;

    public function __construct(
        Dispatcher $dispatcher,
        callable $default,
        array $arguments
    ) {
        $this->dispatcher = $dispatcher;
        $this->default = $default;
        $this->arguments = $arguments;
    }

    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->dispatcher->getNextMiddleware();
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
        $response = call_user_func($this->default, ...$this->arguments);
        if ($response instanceof ResponseInterface) {
            return $response;
        }
        return $this->dispatcher->getErrorResponse();
    }
}