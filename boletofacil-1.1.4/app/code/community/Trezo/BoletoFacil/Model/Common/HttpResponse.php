<?php

class Trezo_BoletoFacil_Model_Common_HttpResponse
{

    private $_status = 200;
    private $_headers = array();
    private $_body;

    /**
     * Define HTTP status request
     * @param $status Int
     * @throws \Exception
     */
    public function setStatus($status)
    {
        if (empty($status)) {
            throw new \Exception("Falha ao executar a requisição, por favor tente mais tarde.");
        }elseif($status == 400){
            throw new \Exception("Request error: " . $this->_body["errorMessage"]);
        }

        $this->_status = $status;
    }

    /**
     * Add HTTP response Header
     * @param $headers String
     * @throws \Exception
     */
    public function addHeaders($headers)
    {
        if (empty($headers)) {
            throw new \Exception("Headers é null. O \"Header\" não pode ser NULL ou vasio.");
        }

        $this->_headers[] = $headers;
    }

    /**
     * Set Response Body
     * @param $body String
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * Return HTTP Response Status
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Return HTTP request Header list
     * @return array String
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Return response Body
     * @return String
     */
    public function getBody()
    {
        return $this->_body;
    }

}
