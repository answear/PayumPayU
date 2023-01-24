<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\ValueObject\Configuration;
use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;

class Api
{
    private ?string $defaultConfig = null;

    protected HttpClientInterface $client;
    protected MessageFactory $messageFactory;
    /** @var array<string, Configuration> */
    protected array $configurations = [];

    public function __construct(array $configurations, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        try {
            Assert::allIsInstanceOf($configurations, Configuration::class);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        if (1 === \count($configurations)) {
            $this->defaultConfig = array_key_first($configurations);
        }

        $this->configurations = $configurations;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    protected function doRequest($method, array $fields): ResponseInterface
    {
        $headers = [];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpointFor(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    protected function getApiEndpointFor(?string $configKey = null): string
    {
        return Environment::Sandbox === $this->getConfig($configKey)->environment
            ? 'http://sandbox.example.com'
            : 'http://example.com';
    }

    private function getConfig(?string $configKey = null): Configuration
    {
        if (null === $this->defaultConfig && null === $configKey) {
            throw new \LogicException('Config key must be provided.');
        }

        return $this->configurations[$configKey ?? $this->defaultConfig];
    }
}
