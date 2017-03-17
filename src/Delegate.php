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

/**
 * Atanvarno\Middleware\Dispatch\Delegate
 *
 * PSR-15 `DelegateInterface` implementation.
 *
 * You should not instantiate this class directly; instead use a dispatcher to
 * manage your middleware queue.
 *
 * @api
 */
class Delegate implements DelegateInterface
{
    /**
     * @internal Class properties.
     *
     * @var array      $arguments  Arguments to pass to end of queue action.
     * @var callable   $default    End of queue action.
     * @var Dispatcher $dispatcher Parent dispatcher.
     */
    private $arguments, $default, $dispatcher;

    /**
     * Builds a `Delegate` instance.
     *
     * @param Dispatcher $dispatcher Parent dispatcher.
     * @param callable   $default    End of queue action.
     * @param array      $arguments  Arguments to pass to end of queue action.
     */
    public function __construct(
        Dispatcher $dispatcher,
        callable $default,
        array $arguments
    ) {
        $this->dispatcher = $dispatcher;
        $this->default = $default;
        $this->arguments = $arguments;
    }

    /**
     * Processes the next available middleware and returns the response.
     *
     * Gets the next middleware from the dispatcher and gives it the request.
     * If no middleware is available (the queue is empty), calls the default
     * action and returns the resulting response. If a response if not
     * returned, falls back on the dispatcher's `getErrorResponse()` method
     * and returns a `500` response.
     *
     * @param ServerRequestInterface $request PSR-7 request to process.
     *
     * @return ResponseInterface PSR-7 response.
     */
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
        return $this->dispatcher->getErrorResponse(500);
    }
}
