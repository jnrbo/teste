<?php

/**
 * @category    Boleto
 * @package     Trezo_BoletoFacil
 * @copyright   André Felipe (contato@trezo.com.br)
 */
class Trezo_BoletoFacil_Block_Info_Boletofacil extends Mage_Payment_Block_Info
{

    protected $_instructions;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payment/info/boletofacil.phtml');
    }

    /**
     * Get instructions text from order payment
     * (or from config, if instructions are missed in payment)
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation('instructions');
            if (empty($this->_instructions)) {
                $this->_instructions = $this->getMethod()->getInstructions();
            }
        }
        return $this->_instructions;
    }

}
