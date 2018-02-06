<?php

class Trezo_BoletoFacil_Block_Form_Boletofacil extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('boletofacil/form/boletofacil.phtml');
    }
}