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

/** Utility package use block. */
use Atanvarno\Middleware\Util\ResponseProvider;

/**
 * Atanvarno\Middleware\Dispatch\BaseDispatcher
 *
 * Base abstract class containing boilerplate dispatcher functionality.
 *
 * @api
 */
abstract class BaseDispatcher extends ResponseProvider implements
    Dispatcher,
    MiddlewareInterface
{
    /** @var array $queue Queue of middleware to process. */
    protected $queue;

    /** @inheritdoc */
    abstract public function getNextMiddleware();

    /** @inheritdoc */
    public function dispatch(
        ServerRequestInterface $request,
        callable $default = null,
        array $arguments = []
    ): ResponseInterface {
        $callable = $default ?? [$this, 'getEmptyResponse'];
        $delegate = new Delegate($this, $callable, $arguments);
        return $delegate->process($request);
    }

    /** @inheritdoc */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ): ResponseInterface {
        $delegateProxy = function(ServerRequestInterface $request)
            use ($delegate) {
                return $delegate->process($request);
        };
        return $this->dispatch($request, $delegateProxy);
    }
}
