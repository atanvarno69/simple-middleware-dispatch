<?php

namespace Atanvarno\Middleware\Dispatch;

use Psr\Http\Message\{
    ResponseInterface, ServerRequestInterface
};
use Interop\Http\ServerMiddleware\DelegateInterface;

class MiddlewarePipe implements DelegateInterface
{
    private $collection, $final;

    public function __construct(
        MiddlewareCollection $middleware,
        DelegateInterface $final
    ) {
        $this->collection = $middleware;
        $this->final = $final;
    }

    public function process(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->collection->isEmpty()) {
            return $this->final->process($request);
        }
        $middleware = $this->collection->pop();
        return $middleware->process($request, $this);
    }
}
