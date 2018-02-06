<?php

class Trezo_BoletoFacil_Model_Cron
{
    /**
     * Get a list of boletos and check if they're paid
     */
    public static function verificaBoletoPago()
    {
        if (!Mage::getStoreConfig('payment/trezo_boletofacil/active')) {
            // Disabled
            Mage::log('trezo_boletofacil: Disabled');
            return false;
        }

        $boletoMethod = Mage::getModel('boletofacil/boletofacilMethod');
        $boletoMethod->log("Verificação boleto ". date('H:i:s d-m-Y'));
        $_boletos = Mage::getModel('boletofacil/boleto')->getCollection()
                ->addFieldToFilter('status', array('eq' => '0'))
                ->addFieldToFilter('payment_token', array('notnull' => true));
        try {
            foreach ($_boletos as $_boleto) {
                $pagamento  = $_boleto->checkPayment();
                $pagamento = $pagamento->getBody();
                if ($pagamento) {
                    $mensagemLog = "Boleto Pago no dia ". date('d/m/Y') ." às ". date('H:i:s') ." no valor de R$ " . $pagamento['data']['payment']['amount'];
                } else {
                    $mensagemLog = date('H:i:s d-m-Y') . ': Pagamento não efetuado';
                }
                $boletoMethod->log($mensagemLog);
            }
        } catch (Exception $e) {
            $boletoMethod->log($e->getMessage());
        }

    }
}
