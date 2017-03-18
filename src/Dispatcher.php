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
use Interop\Http\ServerMiddleware\MiddlewareInterface;

/**
 * Atanvarno\Middleware\Dispatch\Dispatcher
 *
 * Interface for PSR-15 middleware dispatchers.
 *
 * @api
 */
interface Dispatcher
{
    /**
     * Dispatches a request through the middleware queue to return a response.
     *
     * Optionally accepts a `callable` to call when the queue becomes empty to
     * supply to middleware expecting a response from the delegate. This
     * `callable` MUST return a PSR-7 response. If no end of queue action is
     * given, an empty PSR-7 will be generated and passed to the middleware.
     *
     * The `callable` will be passed the request as its first argument. You
     * may specify additional arguments to pass.
     *
     * @param ServerRequestInterface $request   PSR-7 request.
     * @param callable|null          $default   End of queue action.
     * @param array                  $arguments Additional arguments for the
     *      end of queue action.
     *
     * @return ResponseInterface PSR-7 response.
     */
    public function dispatch(
        ServerRequestInterface $request,
        callable $default = null,
        array $arguments = []
    ): ResponseInterface;

    /**
     * Gets the next middleware in the queue.
     *
     * @internal This method is for the delegate's use. You should not call
     *      it directly.
     *
     * @return MiddlewareInterface|null The next middleware. `null` if the
     *      queue is empty.
     */
    public function getNextMiddleware();

    /**
     * Gets an empty PSR-7 response with the given error code.
     *
     * Uses `500` if the given code is not a valid HTTP error code.
     *
     * This method is intended to be used by the delegate. You may call it if
     * you need an error response.
     *
     * @param int $code HTTP error code.
     *
     * @return ResponseInterface PSR-7 response.
     */
    public function getErrorResponse(int $code = 500): ResponseInterface;
}
