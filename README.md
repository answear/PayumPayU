# PayU payment gateway for [payum](http://payum.org/)

This is Symfony bundle, but can be used as php library as well.

PayU documentation: https://developers.payu.com/pl/restapi.html

---

Provide configuration for `payum`

```yaml
answear_payum_pay_u:
    environment: 'sandbox'
    configs:
        pos_1234:
            pos_id: '1234'
            signature_key: 'signature_key'
            oauth_client_id: 'oauth_client_id'
            oauth_secret: 'oauth_secret'
        pos_5678:
            pos_id: '5678'
            signature_key: 'signature_key'
            oauth_client_id: 'oauth_client_id'
            oauth_secret: 'oauth_secret'
    logger: 'Psr\Log\LoggerInterface'
```

`logger` path is not required. Used if you want log some requests and responses. Provide service name to get definition.



---

```yaml
payum:
    gateways:
        payu:
            factory: payu
            payum.action.capture: '@Answear\Payum\PayU\Action\CaptureAction'
            payum.action.refund: '@Answear\Payum\PayU\Action\RefundAction'
            payum.action.notify: '@Answear\Payum\PayU\Action\NotifyAction'
            payum.action.status: '@Answear\Payum\PayU\Action\StatusAction'
            payum.action.convert_payment: '@Answear\Payum\PayU\Action\ConvertPaymentAction'
            payum.action.sync_payment: '@Answear\Payum\PayU\Action\SyncPaymentAction'
            payum.action.cancel: '@Answear\Payum\PayU\Action\CancelAction'
```

Need to provide all `payum.action` as a service.

---

### Capture action

```php
$captureRequest = new Capture($captureToken);
$captureRequest->setModel($payment);
$captureRequest->setModel($payment->getDetails());
$gateway->execute($captureRequest);
```

---

### Missing features

* `OrderRequest` params `recurring`, `mcpData`, `credit`
* ...
