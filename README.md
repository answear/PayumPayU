# PayU payment gateway for [payum](http://payum.org/)

(or for standalone usage)

PayU documentation: https://developers.payu.com/pl/restapi.html

---

Provide configuration for `payum`

```yaml
payum:
    gateways:
        payu:
            factory: payu
            configs:
                first:
                    environment: 'sandbox'
                    pos_id: 'pos_id'
                    signature_key: 'signature_key'
                    oauth_client_id: 'oauth_client_id'
                    oauth_secret: 'oauth_secret'
                second:
                    environment: 'secure'
                    pos_id: 'secure_pos_id'
                    signature_key: 'secure_signature_key'
                    oauth_client_id: 'secure_oauth_client_id'
                    oauth_secret: 'secure_oauth_secret'
```

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

`OrderRequest` params `cardOnFile`, `recurring`, `mcpData`, `threeDsAuthentication`, `credit`
