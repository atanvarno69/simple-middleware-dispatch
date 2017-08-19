<?php

namespace Atanvarno\Middleware\Dispatch;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

interface MiddlewareCollection
{
    public function isEmpty(): bool;

    /**
     * @throws \UnderflowException The collection is empty.
     *
     * @return MiddlewareInterface
     */
    public function pop(): MiddlewareInterface;
}
