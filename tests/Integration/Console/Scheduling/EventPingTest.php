<?php

namespace Illuminate\Tests\Integration\Console\Scheduling;

use Mockery as m;
use GuzzleHttp\HandlerStack;
use Orchestra\Testbench\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Scheduling\Event;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;

/**
 * @group integration
 */
class EventPingTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testPingRescuesTransferExceptions()
    {
        $this->spy(ExceptionHandler::class)
            ->shouldReceive('report')
            ->once()
            ->with(m::type(ServerException::class));

        $httpMock = new HttpClient([
            'handler' => HandlerStack::create(
                new MockHandler([new Psr7Response(500)])
            ),
        ]);

        $this->swap(HttpClient::class, $httpMock);

        $event = new Event(m::mock(EventMutex::class), 'php -i');

        $thenCalled = false;

        $event->pingBefore('https://httpstat.us/500')
            ->then(function () use (&$thenCalled) {
                $thenCalled = true;
            });

        $event->callBeforeCallbacks($this->app->make(Container::class));
        $event->callAfterCallbacks($this->app->make(Container::class));

        $this->assertTrue($thenCalled);
    }
}
