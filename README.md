# http-headers - The HTTP Header parsing library for PHP

`Accept: text/feedback, application/pull-requests`

SHOULD be interpreted as "*This is a work in progress. Early feedback and pull requests are accepted*"

HTTP is hard and you're probably dealing with headers the wrong way. Do you know what `Accept-Encoding: identity` means? What about a `qvalue`? How about this nonsense:

```
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json
```
We didn't think so but guess what? You don't have to! That is right, http-headers can parse this for you and just let you know if the requestor accepts what you want to give them and even ask which one they prefer.

Check out how easy this is!

```php
use Trii\HTTPHeaders;

$accept = new HTTPHeaders\Accept('audio/*; q=0.2, audio/basic');

var_dump($accept->isAccepted('audio/mpeg'));
// bool(false)

var_dump($accept->getPreferredType());
// string(11) "audio/basic"

```

Awesome right?!?
