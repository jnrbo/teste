<?php

class Trezo_BoletoFacil_Adminhtml_BoletofacilController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Made a new requesto to the boletofacil's server to regenerate boleto whit new due date;
     */
    public function regerarAction()
    {
        //Esse método precisa res alterado;
        if ($this->getRequest()->getParam('isAjax')) {
            $response = array('msg' => '', 'success' => false);

            $boleto_id = $this->getRequest()->getParam('boleto_id');
            if ($boleto_id) {
                try {
                    $boleto = Mage::getModel('boletofacil/boleto')->load($boleto_id);
                    if ($boleto) {
                        /** Gerar novo boleto **/
                        $order = Mage::getModel('sales/order')->loadByIncrementId($boleto->getOrderId());
                        $response['url'] = $boleto->generate($order, true)->getUrl();
                        $response['success'] = true;
                    } else {
                        $response['msg'] = 'Boleto não encontrado';
                    }
                } catch (Exception $e) {
                    $response['msg'] = $e->getMessage();
                }
            } else {
                $response['msg'] = 'Boleto não informado.';
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }

    /**
     * Made a requesto to the BoletoFacil's server to check if boleto is paid and check boleto's data;
     */
    public function verificarAction()
    {
        if ($this->getRequest()->getParam('isAjax')) {
            $response = array('msg' => '', 'success' => false);

            $boleto_id = $this->getRequest()->getParam('boleto_id');
            if ($boleto_id) {
                try {
                    $boleto = Mage::getModel('boletofacil/boleto')->load($boleto_id);
                    if ($boleto) {
                        try {
                            $pagamento = $boleto->checkPayment();
                            $pagamento = $pagamento->getBody();
                            if ($pagamento) {
                                $response['success'] = true;
                                $response['pagamento']['Status'] = $boleto->getStatus(true);
                                $response['pagamento']['Valor'] = $pagamento['data']['payment']['amount'];
                                $response['msg'] = "Boleto Pago no dia ". date('d/m/Y') ." às ". date('H:i:s') ." no valor de R$ " . $pagamento['data']['payment']['amount'];
                            } else {
                                $response['msg'] = date('H:i:s d-m-Y') . ': Pagamento não efetuado';
                            }
                        } catch (\Exception $e) {
                            $response['msg'] = $e->getMessage();
                        }
                    } else {
                        $response['msg'] = date('H:i:s d-m-Y') . ': Boleto não encontrado';
                    }
                } catch (Exception $e) {
                    $response['msg'] = $e->getMessage();
                }
            } else {
                $response['msg'] = date('H:i:s d-m-Y') . ': Boleto não informado.';
            }
            $boletoMethod = Mage::getModel('boletofacil/boletofacilMethod');
            $boletoMethod->log($response['msg']);
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }
} 