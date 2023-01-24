<?php

declare(strict_types=1);

namespace Answear\OverseasBundle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

trait MockGuzzleTrait
{
    protected array $clientHistory = [];
    protected MockHandler $guzzleHandler;

    public function setupGuzzleClient(): Client
    {
        $this->guzzleHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->guzzleHandler);

        $this->clientHistory = [];
        $history = Middleware::history($this->clientHistory);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }

    public function mockGuzzleResponse(Response $response)
    {
        $this->guzzleHandler->append($response);
    }
}
