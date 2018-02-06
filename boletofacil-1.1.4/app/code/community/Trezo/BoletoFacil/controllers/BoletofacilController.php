<?php

class Trezo_BoletoFacil_BoletofacilController extends Mage_Core_Controller_Front_Action
{
    /**
     * Receive a request with charge code and payment token notincing that the payment was made;
     */
    public function notificacaoAction()
    {
        if ($this->getRequest()->getParam('chargeCode')) {
            $chargeCode = $this->getRequest()->getParam('chargeCode');
            $boleto = Mage::getModel('boletofacil/boleto')->loadByBoletoFacilCode($chargeCode);
            if ($boleto->getId()) {
                $paymentToken = $this->getRequest()->getParam('paymentToken');
                if(!empty($paymentToken)) {
                    $boleto->setPaymentToken($paymentToken);
                    $boleto->save();
                    header("HTTP/1.1 200 OK");
                    die();
                }
            }
        }
        header("Status: 406 Not Acceptable");
    }
}