<?php

class Trezo_BoletoFacil_Block_Adminhtml_Sales_Order_View_Tab_Boleto extends Mage_Adminhtml_Block_Sales_Order_Abstract implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('boletofacil/boleto.phtml');
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * Retrive Trezo BoletoFacil Boleto instance
     * @return Trezo_BoletoFacil_Model_Boleto
     */
    public function getBoleto()
    {
        return Mage::getModel('boletofacil/boleto')->loadByOrderId($this->getOrder()->getIncrementId());
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Boleto Fácil - Boleto');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Boleto Fácil - Boleto');
    }

    public function canShowTab()
    {
        return ($this->getBoleto()) ? true : false;
    }

    public function isHidden()
    {
        return ($this->getBoleto()->getId()) ? false : true;
    }

}
