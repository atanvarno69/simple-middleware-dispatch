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

/** PSR-17 use block. */
use Interop\Http\Factory\{
    ResponseFactoryInterface, StreamFactoryInterface
};

/** Utility package use block. */
use Atanvarno\Middleware\Util\ResponseProvider;

abstract class BaseDispatcher extends ResponseProvider implements
    Dispatcher,
    MiddlewareInterface
{
    protected $queue;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        parent::__construct($responseFactory, $streamFactory);
    }

    abstract public function getNextMiddleware();

    public function dispatch(
        ServerRequestInterface $request,
        callable $default = null,
        array $args = []
    ): ResponseInterface {
        if (!isset($default)) {
            $default = [$this, 'getEmptyResponse'];
        }
        $delegate = new Delegate($this, $default, $args);
        return $delegate->process($request);
    }

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
