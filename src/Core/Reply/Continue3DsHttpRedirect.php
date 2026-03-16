<?php
namespace Answear\Payum\PayU\Core\Reply;

use Payum\Core\Reply\HttpRedirect;

class Continue3DsHttpRedirect extends HttpRedirect
{
    public function __construct(public readonly bool $iframeAllowed, string $url, int $statusCode = 302, array $headers = array())
    {
        parent::__construct($url, $statusCode, $headers);
    }
}
