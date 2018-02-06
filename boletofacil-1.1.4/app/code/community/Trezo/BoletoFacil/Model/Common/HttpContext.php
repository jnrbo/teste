<?php

class Trezo_BoletoFacil_Model_Common_HttpContext
{

    private $headers = array();
    private $method = 'get';
    private $body = '';
    private $host = '';

    /**
     * Add request header
     * @param $header String
     * @throws \Exception
     */
    public function addHeader($header)
    {
        if (empty($header)) {
            throw new \Exception("Header é nulo");
        }

        if (!in_array($header, $this->getHeaders()))
            $this->headers[] = $header;
    }

    /**
     * Set request method description
     * @param $method String
     * @throws \Exception
     */
    public function setMethod($method)
    {
        if (empty($method)) {
            throw new \Exception("Method é nulo");
        }

        $method = strtolower($method);

        if (!in_array($method, array('get', 'post', 'put', 'delete'))) {
            throw new \Exception("Method é inválido");
        }

        $this->method = $method;
    }

    /**
     * Set request body
     * @param $body String
     * @throws \Exception
     */
    public function setBody($body)
    {
        if (empty($body)) {
            throw new \Exception("Body é nulo, este argumento deve ser informado");
        }

        if ($this->method == "post") {
            $this->body = $body;
        } elseif ($this->method == "get") {
            $formatedBody = array();
            foreach ($body as $key => $value) {
                $formatedBody[] = "{$key}={$value}";
            }
            if(empty($formatedBody)){
                throw new \Exception("Body é inválido");
            }

            $this->body = implode("&", $formatedBody);
        }
    }

    /**
     * Define request host
     * @param $host String
     * @throws \Exception
     */
    public function setHost($host)
    {
        if (empty($host)) {
            throw new \Exception("Host é nulo, este argumento deve ser informado");
        }

        $this->host = $host;
    }

    /**
     * Return header request list
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return request method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return request body
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return request host
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

}
