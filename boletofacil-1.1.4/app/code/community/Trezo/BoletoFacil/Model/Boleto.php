<?php

class Trezo_BoletoFacil_Model_Boleto extends Mage_Core_Model_Abstract
{

    private $data = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('boletofacil/boleto');
    }

    /**
     * Made a request to the boleto facil server sending order and config data to generate/regenerate boleto;
     * @param $order Mage_Sales_Model_Order
     * @param bool $regenerate Check if it'll generate or regenerate boleto
     * @return $this|Trezo_BoletoFacil_Model_Boleto
     * @throws Exception
     */
    public function generate($order, $regenerate = false)
    {
        $boleto = $this->loadByOrderId($order->getIncrementId());
        if (!$boleto->getId() || $regenerate) {
            $response = null;
            $this->setConfigData();
            $this->setOrderData($order);
            if (!empty($this->data)) {
                $webService = Mage::getModel('boletofacil/Webservice');
                $response = $webService->Post($this->data);
                $body = $response->getBody();
                if (isset($body['data']['charges'][0]['link'])) {
                    $this->setUrl($body['data']['charges'][0]['link']);
                    $this->setBoletoFacilCode($body['data']['charges'][0]['code']);
                    $this->setStatus(0);
                    $this->save();
                    $boleto = $this;
                } else {
                    throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar os dados para geração do boleto.");
                }
            } else {
                throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar os dados para geração do boleto.");
            }
        }
        return $boleto;
    }

    /**
     * Check if there is a payment token and if true, made a request to the boleto facil server to check if boleto is paid;
     * @return Trezo_BoletoFacil_Model_Common_HttpResponse
     * @throws \Exception
     */
    public function checkPayment()
    {
        $boletoMethod = Mage::getModel('boletofacil/boletofacilMethod');
        if ($this->getId()) {
            if ($this->getPaymentToken()) {
                $webService = Mage::getModel('boletofacil/Webservice');
                $webService->setHost($webService->getPaymentHost());
                $response = $webService->Post(array('paymentToken' => $this->getPaymentToken()));
                $responseBody = $response->getBody();
                if (isset($responseBody['data']['payment']['amount'])) {
                    $_order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
                    $boletoMethod = Mage::getModel('boletofacil/boletofacilMethod');
                    $dataPagamento = $responseBody['data']['payment']['date'];
                    $valorPago = $responseBody['data']['payment']['amount'];

                    if($this->getValor() >= $valorPago){
                        $this->setStatus('1');
                        $_order->addStatusHistoryComment("BOLETO PAGO EM: {$dataPagamento} | R$ {$valorPago}");
                    }else{
                        $this->setStatus('2');
                        $_order->addStatusHistoryComment("BOLETO PAGO EM: {$dataPagamento} | R$ {$valorPago} Valor inválido pago!");
                    }

-                   $_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $_order->setStatus($boletoMethod->getConfig('order_paid_status'));
-                   $_order->save();

                    /*
                    * Antes o módulo simplesmente alterava o status do
                    * pedido, sem criar a fatura, o que resultava em
                    * problemas no processo da venda.
                    *
                    * Agora criamos a fatura do pedido
                    */
                    try {
                        if(!$_order->canInvoice()){
                            Mage::log(Mage::helper('core')->__('Cannot create an invoice.'));
                        }

                        $invoice = Mage::getModel('sales/service_order', $_order)->prepareInvoice();

                        if (!$invoice->getTotalQty()) {
                            Mage::log(Mage::helper('core')->__('Cannot create an invoice without products.'));
                        }

                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());

                        $transactionSave->save();
                    } catch (Mage_Core_Exception $e) {
                        Mage::logException($e);
                    }

                    $this->setValorPago($valorPago)
                        ->setUpdatedTime(date('s:i:H d-m-Y'))
                        ->save();
                    return $response;
                }
            } else {
                $mensagemErro = "Não foi possível verificar o pagamento.<br />PaymentToken não encontrado, por favor aguarde até que o Boleto Fácil envie a confirmação do pagamento.";
                $boletoMethod->log($mensagemErro . " " . date('s:i:H d-m-Y'));
                throw new \Exception($mensagemErro);
            }
        } else {
            $mensagemErro = date('H:i:s d-m-Y') . ": Não foi possível verificar o pagamento. <br /> Boleto não encontrado.";
            $boletoMethod->log($mensagemErro);
            throw new \Exception($mensagemErro);
        }
    }

    /**
     * Get order data and set it to the request body;
     * @param $order Mage_Sales_Model_Order
     * @throws \Exception
     */
    private function setOrderData($order)
    {
        //Obrigatórios, retornar erro em caso de vazio;
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!empty($order->getGrandTotal())) {
            $this->data['amount'] = $order->getGrandTotal();
            $this->setValor($this->data['amount']);
        } else {
            throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar o total do pedido.");
        }
        if (!empty($order->getCustomerName())) {
            $this->data['payerName'] = $order->getCustomerName();
            $this->setNome($this->data['payerName']);
        } else {
            throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar o nome do cliente.");
        }
        if (!empty($customer->getTaxvat())) {
            $this->data['payerCpfCnpj'] = $customer->getTaxvat();
            $this->setCpfCnpj($this->data['payerCpfCnpj']);
        } else {
            throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar o CPF/CNPJ do cliente.");
        }
        if (!empty($order->getIncrementId())) {
            $this->data['reference'] = $order->getIncrementId();
            $this->setOrderId($this->data['reference']);
        } else {
            throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar o código do pedido.");
        }

        //Definir aqui os dados não obrigatórios;
        if ($customer->getEmail()) {
            $this->data['payerEmail'] = $customer->getEmail();
        }

    }

    /**
     * Get config data and set it to the request body
     * @throws \Exception
     */
    private function setConfigData()
    {
        $boletoMethod = Mage::getModel('boletofacil/boletofacilMethod');

        //Obrigatórios, retornar erro em caso de vazio;
        if (!empty($boletoMethod->getConfig('token'))) {
            $this->data['token'] = $boletoMethod->getConfig('token');
        } else {
            throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar o token de geração do boleto.");
        }
        if (!empty($boletoMethod->getConfig('description'))) {
            $this->data['description'] = $boletoMethod->getConfig('description');
        } else {
            throw new \Exception("Não foi possível efetuar a geração do boleto <br /> Falha ao recuperar a descrição do boleto.");
        }

        //Definir aqui os dados não obrigatórios;
        if (!empty($boletoMethod->getConfig('notify_payer'))) {
            $this->data['notifyPayer'] = "true";
        } else {
            $this->data['notifyPayer'] = "false";
        }
        if (!empty($boletoMethod->getConfig('test'))) {
            $this->data['test'] = $boletoMethod->getConfig('test');
        }
        if (!empty($boletoMethod->getConfig('due_date'))) {
            $date = date("Y-m-d"); // Data de hoje
            $vencimento = $boletoMethod->getConfig('due_date');
            $vencimento = strtotime($date . "+ {$vencimento} days");
            $this->data['dueDate'] = date("d/m/Y", $vencimento); // Soma dias na data; Data de vencimento do boleto;
            $this->setDataVencimento(date("Y-m-d", $vencimento));
            if ($this->getId()) {
                $this->setUpdatedTime($date);
            }
        }
        if (!empty($boletoMethod->getConfig('max_overdue_days'))) {
            $this->data['maxOverdueDays'] = $boletoMethod->getConfig('max_overdue_days');
            if ($this->data['maxOverdueDays'] > 0) {
                $this->data['fine'] = $boletoMethod->getConfig('fine');
                $this->data['interest'] = $boletoMethod->getConfig('interest');
            }
        }
    }

    /**
     * Load boleto by order_id
     * @param $orderId Int
     * @return $this
     */
    public function loadByOrderId($orderId)
    {
        $item = $this->getCollection()->addFieldToFilter('order_id', array('in' => $orderId))->getFirstItem();
        if ($item) {
            return $item;
        }
        return $this;
    }

    /**
     * Load boleto by boleto_facil_code
     * @param $boletoFacilCode Int
     * @return null
     */
    public function loadByBoletoFacilCode($boletoFacilCode)
    {
        $item = $this->getCollection()->addFieldToFilter('boleto_facil_code', array($boletoFacilCode))->getFirstItem();
        if ($item) {
            return $item;
        }
        return $this;
    }

    /**
     * Return boleto_id
     * @return Int
     */
    public function getId()
    {
        return $this->getBoletoId();
    }

    /**
     * Return boleto_status formated or not
     * @param bool $formated
     * @return string
     */
    public function getStatus($formated = false)
    {
        if(!$formated){
            return parent::getStatus();
        }else{
            switch (parent::getStatus()){
                case 0:
                    return "Não Pago";
                    break;
                case 1:
                    return "Pago";
                    break;
                case 2:
                    return "Pago com valor incorreto";
                    break;
            }
        }
    }
}