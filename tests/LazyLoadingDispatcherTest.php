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

/** Container use block. */
use Atanvarno\Dependency\Container;

/** Package use block. */
use Atanvarno\Middleware\Dispatch\{
    Dispatcher, LazyLoadingDispatcher, SimpleDispatcher
};

class LazyLoadingDispatcherTest extends TestCase
{
    /** @var Container $container */
    private $container;

    /** @var LazyLoadingDispatcher $dispatcher */
    private $dispatcher;

    /** @var [] $errors */
    private $errors;

    /** @var MiddlewareInterface $middlewareA */
    private $middlewareA;

    /** @var  MiddlewareInterface $middlewareB */
    private $middlewareB;

    /** @var ServerRequestInterface $request */
    private $request;

    public function setUp()
    {
        $this->container = new Container();
        $this->dispatcher = new LazyLoadingDispatcher(
            new ResponseFactory(), new StreamFactory(), $this->container
        );
        $this->errors = [];
        $this->middlewareA = new class() implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request, DelegateInterface $delegate
            ) {
                $response = $delegate->process($request);
                return $response->withHeader('Test-A', 'OK');
            }
        };
        $this->middlewareB = new class() implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request, DelegateInterface $delegate
            ) {
                $response = $delegate->process($request);
                return $response->withHeader('Test-B', 'OK');
            }
        };
        $this->request = (new ServerRequestFactory())
            ->createServerRequest($_SERVER);
        $this->container->add('A', $this->middlewareA);
        $this->container->add('B', $this->middlewareB);
        set_error_handler([$this, "errorHandler"]);
    }

    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(MiddlewareInterface::class, $this->dispatcher);
        $this->assertInstanceOf(Dispatcher::class, $this->dispatcher);
    }

    public function testConstructorAddsMiddleware()
    {
        $dispatcher = new LazyLoadingDispatcher(
            new ResponseFactory(), new StreamFactory(), $this->container, 'A'
        );
        $this->assertAttributeEquals(['A'], 'queue', $dispatcher);
    }

    public function testAppend()
    {
        $dispatcher = new LazyLoadingDispatcher(
            new ResponseFactory(), new StreamFactory(), $this->container, 'A'
        );
        $result = $dispatcher->append('B');
        $this->assertAttributeEquals(
            ['A', 'B'], 'queue', $dispatcher
        );
        $this->assertSame($dispatcher, $result);
    }

    public function testPrepend()
    {
        $dispatcher = new LazyLoadingDispatcher(
            new ResponseFactory(), new StreamFactory(), $this->container, 'A'
        );
        $result = $dispatcher->prepend('B');
        $this->assertAttributeEquals(
            ['B', 'A'], 'queue', $dispatcher
        );
        $this->assertSame($dispatcher, $result);
    }

    public function testGetNextMiddleware()
    {
        $this->dispatcher->append('A');
        $this->assertSame(
            $this->middlewareA, $this->dispatcher->getNextMiddleware()
        );
        $this->assertSame(
            null, $this->dispatcher->getNextMiddleware()
        );
    }

    public function testDispatchWithNoQueue()
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

    public function testDispatchWithQueue()
    {
        $this->dispatcher->append('A');
        $result = $this->dispatcher->dispatch($this->request);
        $this->assertSame('OK', $result->getHeaderLine('Test-A'));
    }

    public function testDispatchWithCustomDefault()
    {
        $callable = function ($param) {
            $this->assertInstanceOf(ServerRequestInterface::class, $param);
            return (new ResponseFactory())->createResponse();
        };
        $result = $this->dispatcher->dispatch($this->request, $callable);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDispatchWithCustomDefaultWithArgs()
    {
        $callable = function ($paramA, $paramB) {
            $this->assertInstanceOf(ServerRequestInterface::class, $paramA);
            $this->assertSame('test', $paramB);
            return (new ResponseFactory())->createResponse();
        };
        $result = $this->dispatcher->dispatch(
            $this->request, $callable, ['test']
        );
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDispatchWithInvalidCustomDefault()
    {
        $callable = function ($param) {
            $this->assertInstanceOf(ServerRequestInterface::class, $param);
            return null;
        };
        $result = $this->dispatcher->dispatch($this->request, $callable);
        $this->assertError(E_USER_WARNING);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            $result->getStatusCode()
        );
    }

    public function testDispatchWithCustomDefaultThrowsException()
    {
        $callable = function ($param) {
            $this->assertInstanceOf(ServerRequestInterface::class, $param);
            throw new \Exception('Test exception');
        };
        $result = $this->dispatcher->dispatch($this->request, $callable);
        $this->assertError(E_USER_WARNING);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            $result->getStatusCode()
        );
    }

    public function testDispatchWithNotSetContainerEntry()
    {
        $this->dispatcher->append('C');
        $result = $this->dispatcher->dispatch($this->request);
        $this->assertError(E_USER_WARNING);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(
            StatusCodeInterface::STATUS_OK, $result->getStatusCode()
        );
    }

    public function testDispatchWithNotMiddlewareContainerEntry()
    {
        $this->dispatcher->append('C');
        $this->container->add('C', 'this is a string');
        $result = $this->dispatcher->dispatch($this->request);
        $this->assertError(E_USER_WARNING);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(
            StatusCodeInterface::STATUS_OK, $result->getStatusCode()
        );
    }

    public function errorHandler(
        int $errno,
        string $errstr,
        string $errfile = '',
        int $errline = null,
        array $errcontext = []
    ) {
        $this->errors[] = compact(
            'errno', 'errstr', 'errfile', 'errline', 'errcontext'
        );
    }

    public function assertError(int $errorType) {
        foreach ($this->errors as $error) {
            if ($error['errno'] === $errorType) {
                $this->assertTrue(true);
                return;
            }
        }
        $msg = sprintf(
            'Error with level %u not found in %s',
            $errorType,
            var_export($this->errors, true)
        );
        $this->assertTrue(false, $msg);
    }
}
