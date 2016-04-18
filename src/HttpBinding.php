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
     * @param $name
     * @param array $arguments
     * @param array|null $options
     * @param null $inputHeaders
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
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $soapRequest->getSoapMessage());
        fseek($stream, 0);
        $this->builder->setSoapMessage(new Stream($stream));
        return $this->builder->getSoapHttpRequest();
    }

    /**
     * Retrieve SOAP messages from PSR-7 HTTP responses
     *
     * @param ResponseInterface $response
     * @param $name
     * @param array|null $output_headers
     * @return mixed
     */
    public function response(ResponseInterface $response, $name, array &$output_headers = null)
    {
        return $this->interpreter->response($response->getBody()->__toString(), $name, $output_headers);
    }
}