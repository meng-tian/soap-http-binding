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
     * @param string $name              The name of the SOAP function to bind.
     * @param array  $arguments         An array of the arguments to the SOAP function.
     * @param array  $options           An associative array of options.
     *                                  The location option is the URL of the remote Web service.
     *                                  The uri option is the target namespace of the SOAP service.
     *                                  The soapaction option is the action to call.
     * @param mixed $inputHeaders       An array of headers to be bound along with the SOAP request.
     * @return RequestInterface
     * @throws RequestException         If SOAP HTTP binding failed using the given parameters.
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

        try {
            return $this->builder->getSoapHttpRequest();
        } catch (RequestException $exception) {
            $stream->close();
            throw $exception;
        }
    }

    /**
     * Retrieve SOAP messages from PSR-7 HTTP responses
     *
     * @param ResponseInterface $response
     * @param string            $name               The name of the SOAP function to unbind.
     * @param array             $outputHeaders      If supplied, this array will be filled with the headers from
     *                                              the SOAP response.
     * @return mixed
     * @throws \SoapFault                           If the underlying SOAP interpreter throws \SoapFault.
     */
    public function response(ResponseInterface $response, $name, array &$outputHeaders = null)
    {
        return $this->interpreter->response($response->getBody()->__toString(), $name, $outputHeaders);
    }
}