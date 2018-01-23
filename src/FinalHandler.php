<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2018 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch;

/** PSR-7 use block. */
use Psr\Http\Message\{
    ResponseInterface as Response, ServerRequestInterface as Request
};

/** PSR-15 use block. */
use Psr\Http\Server\RequestHandlerInterface as Handler;

/** PSR-17 use block. */
use Interop\Http\Factory\ResponseFactoryInterface as ResponseFactory;

/**
 * Atanvarno\Middleware\Dispatch\FinalHandler
 *
 * @api
 */
class FinalHandler implements Handler
{
    /**
     * @internal Class property.
     *
     * @var ResponseFactory $factory Response factory.
     */
    private $factory;

    /**
     * Builds a `FinalHandler` instance.
     *
     * @param ResponseFactory $factory PSR-17 response factory.
     */
    public function __construct(ResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handles a request and produces a response.
     *
     * Note: this method will discard the request and return a prototype
     * response.
     *
     * @param Request $request Request to handle.
     *
     * @return Response Produced response.
     */
    public function handle(Request $request): Response
    {
        return $this->factory->createResponse();
    }
}
