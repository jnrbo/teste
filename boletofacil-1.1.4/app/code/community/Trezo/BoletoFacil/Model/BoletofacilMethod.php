<?php

class Trezo_BoletoFacil_Model_BoletofacilMethod extends Mage_Payment_Model_Method_Banktransfer
{
    const PAYMENT_METHOD_BANKTRANSFER_CODE = 'trezo_boletofacil';

    protected $webserivce = null;
    protected $_code = self::PAYMENT_METHOD_BANKTRANSFER_CODE;
    protected $_infoBlockType = 'boletofacil/info_boletofacil';

    /**
     * Return trezo_boletofacil admin config
     * @param $key String
     * @return String
     */
    public function getConfig($key)
    {
        return Mage::getStoreConfig("payment/{$this->_code}/{$key}");
    }

    /**
     * Set message to magento log
     * @param $msg String
     */
    public function log($msg)
    {
        Mage::log($msg, null, $this->_code . '.log');
    }
}