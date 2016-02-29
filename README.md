# SOAP HTTP Binding
[![Build Status](https://travis-ci.org/meng-tian/soap-http-binding.svg?branch=master)](https://travis-ci.org/meng-tian/soap-http-binding)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/meng-tian/soap-http-binding/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/meng-tian/soap-http-binding/?branch=master)
[![codecov.io](https://codecov.io/github/meng-tian/soap-http-binding/coverage.svg?branch=master)](https://codecov.io/github/meng-tian/soap-http-binding?branch=master)

This library binds `SOAP 1.1` and `SOAP 1.2` request messages to PSR-7 HTTP requests.

## Usage

```php
// build a PSR-7 request that embedding a SOAP 1.1 request message
$builder = new RequestBuilder();
$request = $builder->isSOAP11()
    ->setEndpoint('http://www.endpoint.com')
    ->setSoapAction('http://www.soapaction.com')
    ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
    ->getSoapHttpRequest();
    
// build a PSR-7 request that embedding a SOAP 1.2 request message
$request = $builder->isSOAP12()
    ->setEndpoint('http://www.endpoint.com')
    ->setSoapAction('http://www.soapaction.com')
    ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
    ->getSoapHttpRequest();

// build a PSR-7 GET request that support SOAP Response message exchange pattern
$request = $builder->isSOAP12()
    ->setEndpoint('http://www.endpoint.com')
    ->getSoapHttpRequest();
```

## License
This library is released under [MIT](https://github.com/meng-tian/soap-http-binding/blob/master/LICENSE) license.

