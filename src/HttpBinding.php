<?php

namespace Meng\Soap\HttpBinding;

use Meng\Soap\Interpreter;
use Psr\Http\Message\RequestInterface;
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
     * @return RequestInterface
     */
    public function toHttpRequest($name, array $arguments, array $options = null, $inputHeaders = null)
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
     * @return mixed
     */
    public function fromHttpResponse($response, $name, &$output_headers = null)
    {
        return $this->interpreter->response($response, $name, $output_headers);
    }
}