<?php

use Meng\Soap\HttpBinding\RequestBuilder;
use Meng\Soap\HttpBinding\RequestException;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;

class RequestBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function soap11Request()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $request = $builder->isSOAP11()
            ->setEndpoint('http://www.endpoint.com')
            ->setSoapAction('http://www.soapaction.com')
            ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
            ->getSoapHttpRequest();

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('text/xml; charset="utf-8"', $request->getHeader('Content-Type')[0]);
        $this->assertTrue($request->hasHeader('Content-Length'));
        $this->assertTrue($request->hasHeader('SOAPAction'));
        $this->assertEquals('http://www.soapaction.com', $request->getHeader('SOAPAction')[0]);
        $this->assertEquals('http://www.endpoint.com', (string)$request->getUri());
    }

    /**
     * @test
     */
    public function soap11RequestHttpGetBinding()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $this->expectException('Meng\Soap\HttpBinding\RequestException');
        $builder->setHttpMethod('GET')
            ->setEndpoint('http://www.endpoint.com')
            ->setSoapAction('http://www.soapaction.com')
            ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
            ->getSoapHttpRequest();
    }

    /**
     * @test
     */
    public function soap12Request()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $request = $builder->isSOAP12()
            ->setEndpoint('http://www.endpoint.com')
            ->setSoapAction('http://www.soapaction.com')
            ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
            ->getSoapHttpRequest();

        $this->assertEquals('POST', $request->getMethod());
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertEquals('application/soap+xml; charset="utf-8"; action="http://www.soapaction.com"', $request->getHeader('Content-Type')[0]);
        $this->assertTrue($request->hasHeader('Content-Length'));
        $this->assertFalse($request->hasHeader('SOAPAction'));
        $this->assertEquals('http://www.endpoint.com', (string)$request->getUri());
    }

    /**
     * @test
     */
    public function soap12RequestPutMethod()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $this->expectException('Meng\Soap\HttpBinding\RequestException');
        $builder->isSOAP12()
            ->setEndpoint('http://www.endpoint.com')
            ->setSoapAction('http://www.soapaction.com')
            ->setHttpMethod('PUT')
            ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
            ->getSoapHttpRequest();
    }

    /**
     * @test
     */
    public function soap12RequestGetMethod()
    {
        $stream = fopen('php://temp', 'w');
        fwrite($stream, 'some string');
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $request = $builder->isSOAP12()
            ->setHttpMethod('GET')
            ->setEndpoint('http://www.endpoint.com')
            ->setSoapAction('http://www.soapaction.com')
            ->setSoapMessage(new Stream($stream, 'r'))
            ->getSoapHttpRequest();

        $this->assertEquals('GET', $request->getMethod());
        $this->assertFalse($request->hasHeader('Content-Type'));
        $this->assertEquals('application/soap+xml', $request->getHeader('Accept')[0]);
        $this->assertFalse($request->hasHeader('Content-Length'));
        $this->assertFalse($request->hasHeader('SOAPAction'));
        $this->assertEquals('http://www.endpoint.com', (string)$request->getUri());
        $this->assertEquals('', $request->getBody()->getContents());
    }

    /**
     * @test
     */
    public function soapNoEndpoint()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $this->expectException('Meng\Soap\HttpBinding\RequestException');
        $builder->setSoapMessage(new Stream(fopen('php://temp', 'r')))->getSoapHttpRequest();
    }

    /**
     * @test
     */
    public function soapNoMessage()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        $this->expectException('Meng\Soap\HttpBinding\RequestException');
        $builder->setEndpoint('http://www.endpoint.com')->getSoapHttpRequest();
    }

    /**
     * @test
     */
    public function resetAllAfterFailure()
    {
        $builder = new RequestBuilder(new StreamFactory(), new RequestFactory());
        try {
            $builder->isSOAP12()->setEndpoint('http://www.endpoint.com')->getSoapHttpRequest();
        } catch (RequestException $e) {
        }
        $this->expectException('Meng\Soap\HttpBinding\RequestException');
        $builder->setHttpMethod('GET')->getSoapHttpRequest();
    }
}
