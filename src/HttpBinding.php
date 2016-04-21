<?php

namespace Meng\Soap\HttpBinding;

use Meng\Soap\Interpreter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Stream;

class HttpBinding
{
    private $interpreter;
    private $builder;

    public function __construct(Interpreter $interpreter, RequestBuilder $builder)
    {
        $this->interpreter = $interpreter;
        $this->builder = $builder;
    }

    /**
     * Embed SOAP messages in PSR-7 HTTP Requests
     *
     * @param string $name
     * @param array $arguments
     * @param array $options
     * @param mixed $inputHeaders
     * @return RequestInterface
     */
    public function request($name, array $arguments, array $options = null, $inputHeaders = null)
    {
        $soapRequest = $this->interpreter->request($name, $arguments, $options, $inputHeaders);
        if ($soapRequest->getSoapVersion() == '1') {
            $this->builder->isSOAP11();
        } else {
            $this->builder->isSOAP12();
        }
        $this->builder->setEndpoint($soapRequest->getEndpoint());
        $this->builder->setSoapAction($soapRequest->getSoapAction());
        $stream = new Stream('php://temp', 'r+');
        $stream->write($soapRequest->getSoapMessage());
        $stream->rewind();
        $this->builder->setSoapMessage($stream);
        return $this->builder->getSoapHttpRequest();
    }

    /**
     * Retrieve SOAP messages from PSR-7 HTTP responses
     *
     * @param ResponseInterface $response
     * @param string $name
     * @param array $outputHeaders
     * @return mixed
     * @throws \SoapFault
     */
    public function response(ResponseInterface $response, $name, array &$outputHeaders = null)
    {
        return $this->interpreter->response($response->getBody()->__toString(), $name, $outputHeaders);
    }
}