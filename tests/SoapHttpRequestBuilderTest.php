<?php

use Meng\Soap\HttpBinding\RequestBuilder;
use Zend\Diactoros\Stream;

class SoapHttpRequestBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function soap11Request()
    {
        $builder = new RequestBuilder();
        $request = $builder->setEndpoint('http://www.endpoint.com')
            ->setHttpMethod('POST')
            ->setSoapAction('http://www.soapaction.com')
            ->setVersion('1.1')
            ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
            ->getSoapHttpRequest();

        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertEquals('text/xml; charset="utf-8"', $request->getHeader('Content-Type')[0]);
        $this->assertTrue($request->hasHeader('Content-Length'));
        $this->assertTrue($request->hasHeader('SOAPAction'));
    }

    /**
     * @test
     */
    public function soap12Request()
    {
        $builder = new RequestBuilder();
        $request = $builder->setEndpoint('http://www.endpoint.com')
            ->setHttpMethod('POST')
            ->setSoapAction('http://www.soapaction.com')
            ->setVersion('1.2')
            ->setSoapMessage(new Stream(fopen('php://temp', 'r')))
            ->getSoapHttpRequest();

        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertEquals('application/soap+xml; charset="utf-8"; action="http://www.soapaction.com"', $request->getHeader('Content-Type')[0]);
        $this->assertTrue($request->hasHeader('Content-Length'));
        $this->assertFalse($request->hasHeader('SOAPAction'));
    }
}
