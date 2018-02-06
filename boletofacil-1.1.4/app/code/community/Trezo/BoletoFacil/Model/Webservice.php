<?php

class Trezo_BoletoFacil_Model_Webservice
{

    private $host = "https://www.boletobancario.com/boletofacil/integration/api/v1/issue-charge";
    private $paymentHost = "https://www.boletobancario.com/boletofacil/integration/api/v1/fetch-payment-details";
    private $default_header = 'Content-Type: application/json';
    const CHECK_PAYMENT_URL = "";

    private $_curl;

    public function __construct()
    {
        //Caso esteja em ambiente de testes, utiliza a URL do ambiente de testes do Boleto Fácil.
        if (Mage::getStoreConfig("payment/trezo_boletofacil/test")) {
            $this->host = "https://sandbox.boletobancario.com/boletofacil/integration/api/v1/issue-charge";
            $this->paymentHost = "https://sandbox.boletobancario.com/boletofacil/integration/api/v1/fetch-payment-details";
        }

        $this->_curl = curl_init();

        if (empty($this->_curl)) {
            throw new \Exception("curl_init is null");
        }
    }

    /**
     * Mount a POST request
     * @param array $json
     * @return Trezo_BoletoFacil_Model_Common_HttpResponse
     * @throws \Exception
     */
    public function Post(array $json)
    {
        $context = Mage::getModel('boletofacil/common_httpContext');
        $context->setMethod('post');
        $context->addHeader($this->default_header);
        $context->setHost($this->getHost());

        $context->setBody($json);
        return $this->Send($context);

    }

    /**
     * Mount a GET request
     * @param array $json
     * @return Trezo_BoletoFacil_Model_Common_HttpResponse
     * @throws \Exception
     */
    public function Get(array $json)
    {
        $context = Mage::getModel('boletofacil/common_httpContext');
        $context->setMethod('get');
        $context->addHeader($this->default_header);
        $context->setBody($json);

        $context->setHost($this->getHost() . '?' . $context->getBody());
        return $this->Send($context);
    }

    /**
     * Execute a CURL Request
     * @param Trezo_BoletoFacil_Model_Common_HttpContext $context
     * @return Trezo_BoletoFacil_Model_Common_HttpResponse
     * @throws \Exception
     */
    public function Send(Trezo_BoletoFacil_Model_Common_HttpContext $context)
    {
        if (empty($context)) {
            throw new \Exception("Context is null");
        }

        curl_setopt($this->_curl, CURLOPT_URL, $context->getHost());
        curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, strtoupper($context->getMethod()));
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $context->getBody());
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);

        if(!Mage::getStoreConfig("payment/trezo_boletofacil/test")) {
            if (substr($context->getHost(), 0, 5) == 'https') {
                curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, 0);
            }
        }
        if (in_array($context->getMethod(), array('post', 'put'))) {
            $context->addHeader('Content-Length: ' . count($context->getBody()));
        }

        $raw_result = curl_exec($this->_curl);

        $pos = strpos($raw_result, "\r\n\r\n");

        $headers = explode("\r\n", substr($raw_result, 0, $pos));

        $response = Mage::getModel('boletofacil/common_httpResponse');
        $response->addHeaders($headers);

        // +4 porque é duplo \r\n que são as 2 linhas no fim do header
        $response->setBody(json_decode($raw_result, true));
        $return_info = curl_getinfo($this->_curl);
        $response->setStatus($return_info['http_code']);
        return $response;
    }

    /**
     * Return Request host
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set Request Host
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Return Payment Host
     * @return string
     */
    public function getPaymentHost()
    {
        return $this->paymentHost;
    }

    /**
     * Return the default Header
     * @return string
     */
    public function getDefaultHeader()
    {
        return $this->default_header;
    }

    /**
     * Set default header
     * @param string $default_header
     */
    public function setDefaultHeader($default_header)
    {
        $this->default_header = $default_header;
    }
}
