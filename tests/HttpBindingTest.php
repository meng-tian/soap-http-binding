<?php

use Meng\Soap\HttpBinding\HttpBinding;
use Meng\Soap\HttpBinding\RequestBuilder;
use Meng\Soap\HttpBinding\RequestException;
use Meng\Soap\Interpreter;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;

class HttpBindingTest extends TestCase
{
    /**
     * @test
     */
    public function soap11()
    {
        $interpreter = new Interpreter(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR . 'airport.wsdl', ['soap_version' => SOAP_1_1]);
        $streamFactory = new StreamFactory();
        $requestFactory = new RequestFactory();
        $builder = new RequestBuilder($streamFactory, $requestFactory);
        $httpBinding = new HttpBinding($interpreter, $builder, $streamFactory);
        $request = $httpBinding->request('GetAirportInformationByCountry', [['country' => 'United Kingdom']]);
        $this->assertTrue($request instanceof \Psr\Http\Message\RequestInterface);

        $response = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetAirportInformationByCountryResponse xmlns="http://www.webserviceX.NET">
      <GetAirportInformationByCountryResult>string</GetAirportInformationByCountryResult>
    </GetAirportInformationByCountryResponse>
  </soap:Body>
</soap:Envelope>
EOD;

        $stream = $streamFactory->createStream();
        $stream->write($response);
        $stream->rewind();
        $response = new Response($stream, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
        $response = $httpBinding->response($response, 'GetAirportInformationByCountry');
        $this->assertObjectHasAttribute('GetAirportInformationByCountryResult', $response);
    }

    /**
     * @test
     */
    public function soap12()
    {
        $interpreter = new Interpreter(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR . 'uszip.wsdl', ['soap_version' => SOAP_1_2]);
        $streamFactory = new StreamFactory();
        $requestFactory = new RequestFactory();
        $builder = new RequestBuilder($streamFactory, $requestFactory);
        $httpBinding = new HttpBinding($interpreter, $builder, $streamFactory);

        $request = $httpBinding->request('GetInfoByCity', [['USCity' => 'New York']]);
        $this->assertTrue($request instanceof \Psr\Http\Message\RequestInterface);

        $response = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <GetInfoByCityResponse xmlns="http://www.webserviceX.NET">
      <GetInfoByCityResult>some information</GetInfoByCityResult>
    </GetInfoByCityResponse>
  </soap12:Body>
</soap12:Envelope>
EOD;

        $stream = $streamFactory->createStream();
        $stream->write($response);
        $stream->rewind();
        $response = new Response($stream, 200, ['Content-Type' => 'Content-Type: application/soap+xml; charset=utf-8']);
        $response = $httpBinding->response($response, 'GetInfoByCity');
        $this->assertObjectHasAttribute('GetInfoByCityResult', $response);
    }

    /**
     * @test
     */
    public function requestBindingFailed()
    {
        $interpreter = new Interpreter(null, ['uri' => '', 'location' => '']);
        $builderMock = $this->getMockBuilder('Meng\Soap\HttpBinding\RequestBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['getSoapHttpRequest'])
            ->getMock();
        $builderMock->method('getSoapHttpRequest')->willThrowException(new RequestException());

        $httpBinding = new HttpBinding($interpreter, $builderMock, new StreamFactory);
        $this->expectException('Meng\Soap\HttpBinding\RequestException');
        $httpBinding->request('some-function', []);
    }
}
