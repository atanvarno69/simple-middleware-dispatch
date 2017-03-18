<?php
/**
 * @package   Atanvarno\Middleware\Dispatch
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Middleware\Dispatch\Test;

/** PSR-7 use block. */
use Psr\Http\Message\{
    ResponseInterface, ServerRequestInterface
};

/** PSR-15 use block. */
use Interop\Http\ServerMiddleware\{
    DelegateInterface, MiddlewareInterface
};

/** HTTP Message Utilities use block. */
use Fig\Http\Message\StatusCodeInterface;

/** PHP Unit use block. */
use PHPUnit\Framework\TestCase;

/** Zend Diactoros use block. */
use Http\Factory\Diactoros\{
    ResponseFactory, ServerRequestFactory, StreamFactory
};

/** Package use block. */
use Atanvarno\Middleware\Dispatch\{
    BaseDispatcher, Dispatcher
};

class BaseDispatcherTest extends TestCase
{
    /**
     * @var BaseDispatcher         $dispatcher
     * @var ServerRequestInterface $request
     */
    private $dispatcher, $request;

    public function setUp()
    {
        $this->dispatcher = $this->getMockForAbstractClass(
            BaseDispatcher::class,
            [new ResponseFactory(), new StreamFactory()]
        );
        $this->request = (new ServerRequestFactory())
            ->createServerRequest($_SERVER);
    }

    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(MiddlewareInterface::class, $this->dispatcher);
        $this->assertInstanceOf(Dispatcher::class, $this->dispatcher);
    }

    public function testDispatch()
    {
        $result = $this->dispatcher->dispatch($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(
            StatusCodeInterface::STATUS_OK, $result->getStatusCode()
        );
        $this->assertTrue($result->getBody()->isReadable());
        $this->assertTrue($result->getBody()->isSeekable());
        $this->assertTrue($result->getBody()->isWritable());
    }

    public function testDispatchWithCallable()
    {
        $callable = function() {
            $this->assertTrue(true);
            return (new ResponseFactory())->createResponse();
        };
        $result = $this->dispatcher->dispatch($this->request, $callable);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDispatchWithCallableWithArguments()
    {
        $callable = function($request, $argumentB) {
            $this->assertTrue($argumentB);
            return (new ResponseFactory())->createResponse();
        };
        $result = $this->dispatcher->dispatch(
            $this->request, $callable, [true]
        );
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcess()
    {
        $delegate = new class($this) implements DelegateInterface {
            private $parent;
            public function __construct($arg) {
                $this->parent = $arg;
            }
            public function process(ServerRequestInterface $request) {
                $this->parent->assertTrue(true);
                return (new ResponseFactory())->createResponse();
            }
        };
        $result = $this->dispatcher->process($this->request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
