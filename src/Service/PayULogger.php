<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Service;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PayULogger extends AbstractLogger
{
    public function __construct(private ?LoggerInterface $logger = new NullLogger())
    {
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, '[Payum] ' . $message, $context + ['gateway' => 'payu']);
    }
}
