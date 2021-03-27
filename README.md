# SOAP HTTP Binding [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/meng-tian/soap-http-binding/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/meng-tian/soap-http-binding/?branch=master) [![codecov.io](https://codecov.io/github/meng-tian/soap-http-binding/coverage.svg?branch=master)](https://codecov.io/github/meng-tian/soap-http-binding?branch=master)

This library binds `SOAP 1.1` and `SOAP 1.2` messages to PSR-7 HTTP messages.

## Requirement
PHP 7.1

## Install
```
composer require meng-tian/soap-http-binding
```

## Usage
`HttpBinding::request` embeds SOAP reqeust messages into PSR-7 HTTP requests.
```php
use Meng\Soap\HttpBinding\HttpBinding;
use Meng\Soap\HttpBinding\RequestBuilder;
use Meng\Soap\Interpreter;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;

$interpreter = new Interpreter('http://www.webservicex.net/airport.asmx?WSDL');
$streamFactory = new StreamFactory();
$requestFactory = new RequestFactory();
$builder = new RequestBuilder($streamFactory, $requestFactory);
$httpBinding = new HttpBinding($interpreter, $builder, $streamFactory);

$request = $httpBinding->request('GetAirportInformationByCountry', [['country' => 'United Kingdom']]);
echo \Laminas\Diactoros\Request\Serializer::toString($request);
```
Output:
```
POST /airport.asmx HTTP/1.1
Content-Length: 322
SOAPAction: http://www.webserviceX.NET/GetAirportInformationByCountry
Content-Type: text/xml; charset="utf-8"
Host: www.webservicex.net

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.webserviceX.NET"><SOAP-ENV:Body><ns1:GetAirportInformationByCountry><ns1:country>United Kingdom</ns1:country></ns1:GetAirportInformationByCountry></SOAP-ENV:Body></SOAP-ENV:Envelope>

```


`HttpBinding::response` retrieves SOAP response messages from PSR-7 HTTP responses: 
```php
use Meng\Soap\HttpBinding\HttpBinding;
use Meng\Soap\HttpBinding\RequestBuilder;
use Meng\Soap\Interpreter;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;

$response = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetAirportInformationByCountryResponse xmlns="http://www.webserviceX.NET">
      <GetAirportInformationByCountryResult>some result</GetAirportInformationByCountryResult>
    </GetAirportInformationByCountryResponse>
  </soap:Body>
</soap:Envelope>
EOD;

$stream = new Stream('php://memory', 'r+');
$stream->write($response);
$stream->rewind();
$response = new Response($stream, 200, ['Content-Type' => 'text/xml; charset=utf-8']);

$interpreter = new Interpreter('http://www.webservicex.net/airport.asmx?WSDL');
$streamFactory = new StreamFactory();
$requestFactory = new RequestFactory();
$builder = new RequestBuilder($streamFactory, $requestFactory);
$httpBinding = new HttpBinding($interpreter, $builder, $streamFactory);
$response = $httpBinding->response($response, 'GetAirportInformationByCountry');

print_r($response);
```
Output:
```
stdClass Object
(
    [GetAirportInformationByCountryResult] => some result
)
```


This library also support `SOAP 1.2` HTTP GET binding through `RequestBuilder` class :
```php
use Meng\Soap\HttpBinding\RequestBuilder;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;

$streamFactory = new StreamFactory();
$requestFactory = new RequestFactory();
$builder = new RequestBuilder($streamFactory, $requestFactory);
$request = $builder->isSOAP12()
    ->setEndpoint('http://www.endpoint.com')
    ->setHttpMethod('GET')
    ->getSoapHttpRequest();
echo \Laminas\Diactoros\Request\Serializer::toString($request);
```
Output:
```
GET / HTTP/1.1
Accept: application/soap+xml
Host: www.endpoint.com
```


## License
This library is released under [MIT](https://github.com/meng-tian/soap-http-binding/blob/master/LICENSE) license.

