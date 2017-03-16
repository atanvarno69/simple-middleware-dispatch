<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch;

/** PSR-7 use block. */
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\{
    ResponseInterface, ServerRequestInterface
};

interface Dispatcher
{
    public function dispatch(
        ServerRequestInterface $request,
        callable $default = null,
        array $args = []
    ): ResponseInterface;

    /**
     * @return MiddlewareInterface|false
     */
    public function getNextMiddleware();

    /**
     * @param int $code HTTP error code.
     *
     * @return ResponseInterface
     */
    public function getErrorResponse(int $code = 500): ResponseInterface;
}
