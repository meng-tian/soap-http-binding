<?php

use Meng\Soap\HttpBinding\RequestBuilder;
use Meng\Soap\HttpBinding\RequestException;
use Zend\Diactoros\Stream;

class RequestBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function soap11Request()
    {
        $builder = new RequestBuilder();
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
     * @expectedException Meng\Soap\HttpBinding\RequestException
     */
    public function soap11RequestHttpGetBinding()
    {
        $builder = new RequestBuilder();
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
        $builder = new RequestBuilder();
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
     * @expectedException Meng\Soap\HttpBinding\RequestException
     */
    public function soap12RequestPutMethod()
    {
        $builder = new RequestBuilder();
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
        $builder = new RequestBuilder();
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
     * @expectedException Meng\Soap\HttpBinding\RequestException
     */
    public function soapNoEndpoint()
    {
        $builder = new RequestBuilder();
        $builder->setSoapMessage(new Stream(fopen('php://temp', 'r')))->getSoapHttpRequest();
    }

    /**
     * @test
     * @expectedException Meng\Soap\HttpBinding\RequestException
     */
    public function soapNoMessage()
    {
        $builder = new RequestBuilder();
        $builder->setEndpoint('http://www.endpoint.com')->getSoapHttpRequest();
    }

    /**
     * @test
     * @expectedException Meng\Soap\HttpBinding\RequestException
     */
    public function resetAllAfterFailure()
    {
        $builder = new RequestBuilder();
        try {
            $builder->isSOAP12()->setEndpoint('http://www.endpoint.com')->getSoapHttpRequest();
        } catch (RequestException $e) {
        }
        $builder->setHttpMethod('GET')->getSoapHttpRequest();
    }
}
