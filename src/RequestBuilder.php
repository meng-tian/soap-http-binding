<?php

namespace Meng\Soap\HttpBinding;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;

/**
 * This class create PSR HTTP requests that embed SOAP messages.
 */
class RequestBuilder
{
    const SOAP11 = '1.1';
    const SOAP12 = '1.2';

    /**
     * @var string
     */
    private $endpoint;
    /**
     * @var string
     */
    private $soapVersion = self::SOAP11;
    /**
     * @var string
     */
    private $soapAction = '';
    /**
     * @var StreamInterface
     */
    private $soapMessage;
    /**
     * @var bool
     */
    private $hasSoapMessage = false;
    /**
     * @var string
     */
    private $httpMethod = 'POST';

    /**
     * @return RequestInterface
     * @throws RequestException
     */
    public function getSoapHttpRequest()
    {
        $this->validate();
        $headers = $this->prepareHeaders();
        $message = $this->prepareMessage();
        $request = new Request(
            $this->endpoint,
            $this->httpMethod,
            $message,
            $headers
        );
        $this->unsetAll();
        return $request;
    }

    /**
     * @param string $endpoint
     * @return self
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return self
     */
    public function isSOAP11()
    {
        $this->soapVersion = self::SOAP11;
        return $this;
    }

    public function isSOAP12()
    {
        $this->soapVersion = self::SOAP12;
        return $this;
    }


    /**
     * @param string $soapAction
     * @return self
     */
    public function setSoapAction($soapAction)
    {
        $this->soapAction = $soapAction;
        return $this;
    }

    /**
     * @param StreamInterface $message
     * @return self
     */
    public function setSoapMessage(StreamInterface $message)
    {
        $this->soapMessage = $message;
        $this->hasSoapMessage = true;
        return $this;
    }

    /**
     * @param string $method
     * @return self
     */
    public function setHttpMethod($method)
    {
        $this->httpMethod = $method;
        return $this;
    }

    private function validate()
    {
        $isValid = true;

        if (!$this->endpoint) {
            $isValid = false;
        }

        if (!$this->hasSoapMessage && $this->httpMethod == 'POST') {
            $isValid = false;
        }

        /**
         * SOAP 1.1 only defines HTTP binding with POST method.
         * @link https://www.w3.org/TR/2000/NOTE-SOAP-20000508/#_Toc478383527
         */
        if ($this->soapVersion == self::SOAP11 && $this->httpMethod != 'POST') {
            $isValid = false;
        }

        /**
         * SOAP 1.2 only defines HTTP binding with POST and GET methods.
         * @link https://www.w3.org/TR/2007/REC-soap12-part0-20070427/#L10309
         */
        if ($this->soapVersion == self::SOAP12 && !in_array($this->httpMethod, ['GET', 'POST'])) {
            $isValid = false;
        }

        if (!$isValid) {
            $this->unsetAll();
            throw new RequestException;
        }
    }

    /**
     * @return array
     */
    private function prepareHeaders()
    {
        if ($this->soapVersion == self::SOAP11) {
            return $this->prepareSoap11Headers();
        } else {
            return $this->prepareSoap12Headers();
        }
    }

    /**
     * @link https://www.w3.org/TR/2000/NOTE-SOAP-20000508/#_Toc478383526
     * @return array
     */
    private function prepareSoap11Headers()
    {
        $headers = [];
        $headers['Content-Length'] = $this->soapMessage->getSize();
        $headers['SOAPAction'] = $this->soapAction;
        $headers['Content-Type'] = 'text/xml; charset="utf-8"';
        return $headers;
    }

    /**
     * SOSPAction header is removed in SOAP 1.2 and now expressed as a value of
     * an (optional) "action" parameter of the "application/soap+xml" media type.
     * @link https://www.w3.org/TR/soap12-part0/#L4697
     * @return array
     */
    private function prepareSoap12Headers()
    {
        $headers = [];
        if ($this->httpMethod == 'POST') {
            $headers['Content-Length'] = $this->soapMessage->getSize();
            $headers['Content-Type'] = 'application/soap+xml; charset="utf-8"' . '; action="' . $this->soapAction . '"';
        } else {
            $headers['Accept'] = 'application/soap+xml';
        }
        return $headers;
    }

    /**
     * @return StreamInterface
     */
    private function prepareMessage()
    {
        if ($this->httpMethod == 'POST') {
            return $this->soapMessage;
        } else {
            return new Stream('php://temp', 'r');
        }
    }

    private function unsetAll()
    {
        $this->endpoint = null;
        if ($this->hasSoapMessage) {
            $this->soapMessage = null;
            $this->hasSoapMessage = false;
        }
        $this->soapAction = '';
        $this->soapVersion = self::SOAP11;
        $this->httpMethod = 'POST';
    }
}