<?php
/**
 * Expercash Expercash
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category    Expercash
 * @package     Expercash_Expercash
 * @copyright   Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Expercash_Expercash_Helper_Payment extends Mage_Core_Helper_Abstract
{

    protected $_expercashStack = false;

    /**
     * @var $dataHelper Expercash_Expercash_Helper_Data
     */
    private $dataHelper = null;


    /**
     * the required params that must be in the reponse
     *
     * @var array $requiredParam
     */
    private $requiredParams
        = array('amount', 'currency', 'paymentMethod', 'transactionId', 'GuTID',
                'exportKey');

    /**
     * setter for data helper
     *
     * @param null $dataHelper
     */
    public function setDataHelper($dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * getter for data helper
     *
     * @return Expercash_Expercash_Helper_Data
     */
    public function getHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('expercash/data');
        }

        return $this->dataHelper;
    }


    public function createExperCashOrderId()
    {
        $orderId = date('Ymd') . '_ED' . substr(rand(0, 1000000), 0, 8);

        // $this->setExperCashOrderId($orderId);

        return $orderId;
    }

    /**
     * save expercash data in db
     *
     * @param integer $id QuoteId
     * @param string  $key
     * @param string  $value
     */
    public function setExperCashData($id, $key, $value)
    {
        $resources = Mage::getSingleton('core/resource');
        $connWrite = $resources->getConnection('core_write');
        $query = $connWrite->update(
            $resources->getTableName('sales_flat_quote_payment'),
            array($key => $value), "`quote_id` = " . $id
        );
    }

    /**
     * return expercash payment data from sales_flat_quote_payment based on payment id
     *
     * @param integer $id QuoteId
     *
     * @return array
     */
    public function getExperCashData($id)
    {
        $resources = Mage::getSingleton('core/resource');
        $connRead = $resources->getConnection('core_read');

        $query = $connRead->select()
            ->from(
                $resources->getTableName('sales_flat_quote_payment'), array(
                    'expercash_request_type',
                    'expercash_epi_payment_id',
                    'expercash_gutid_capture',
                    'expercash_gutid',
                    'expercash_transaction_id',
                    'expercash_paymenttype'
                )
            )
            ->where("`quote_id` = " . $id);

        return $connRead->fetchAll($query);
    }

    /**
     * get expercash info
     *
     * @param $string
     * @param $id
     *
     * @return bool
     */
    public function getExperCashInfo($string, $id)
    {
        if ($this->_expercashStack === false) {
            $this->_expercashStack = $this->getExperCashData($id);
        }

        return isset($this->_expercashStack[0][$string])
            ? $this->_expercashStack[0][$string] : false;
    }

    /**
     * send invoice to customer if that was configured by the merchant
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice Invoice to be sent
     *
     * @return void
     */
    public function sendInvoiceToCustomer(
        Mage_Sales_Model_Order_Invoice $invoice
    ) {
        $code = $invoice->getOrder()->getPayment()->getMethod();
        $storeId = $invoice->getOrder()->getStoreId();
        if (false == $invoice->getEmailSent()
            && Mage::getModel('expercash/config')->getConfigData(
                'sendinvoicemail', $code, $storeId
            )
        ) {
            $invoice->sendEmail($notifyCustomer = true);
        }
    }

    /**
     * check and handle the response data
     *
     * @param $response array
     *
     * @return bool
     */
    public function checkReturnedData($response)
    {

        if (!$this->checkResponseParams($response)) {
            Mage::throwException(
                $this->getHelper()->__('Response is not correct.')
            );
        }
        $order = $this->getOrder($response['transactionId']);
        if (!$this->checkOrderDependentConditions($order, $response)) {
            Mage::throwException(
                $this->getHelper()->__('Response is not correct.')
            );
        }
        $storeId = $order->getStoreId();
        $checksum = $this->getCheckSum($response, $storeId, $order);
        $this->getHelper()->log(
            sprintf(
                "Check Hash of response from ExperCash:\n" .
                "Module-Checksum: %s\nExperCash-Checksum: %s",
                $checksum,
                $response["exportKey"]
            )
        );
        $payment = $order->getPayment();
        if ($checksum == $response["exportKey"]) {
            /* @var $expercash Expercash_Expercash_Model_Expercash */
            $expercash = $order->getPayment()->getMethodInstance();

            $this->setExpercashRef($response, $expercash, $order);
            $this->setTxData($response, $expercash, $order, $storeId);
            $this->setPaymentStatus($payment);
            //Save additional payment information to order-payment
            $expercash->setAdditionalPaymentInfo($payment, $response);
            $this->setOrderStatus($response, $expercash, $order);

            if (false == $order->getEmailSent()) {
                $order->sendNewOrderEmail();
                $order->save();
            }

            $this->createInvoice($expercash, $order, $response);
            $status = true;
            $this->updateOrder($payment, $order);
        } else {
            $this->cancelOrder($order);
            $status = false;
        }

        return $status;
    }

    /**
     * build and return the checksum from the response params
     *
     * @param $response
     * @param $storeId
     *
     * @return string
     */
    protected function getCheckSum($response, $storeId, $order)
    {
        $result = $response["amount"]
            . $response["currency"]
            . $response["paymentMethod"]
            . $response["transactionId"]
            . $response["GuTID"];

        // if payment is barzahlen include paymentStatus param in checksum
        if ($order->getPayment()->getMethod()
            == Expercash_Expercash_Model_Expercashpc::PAYMENT_METHOD_NAME
        ) {
            $result .= $response["paymentStatus"];
        }

        return md5(
            $result . Mage::getModel('expercash/config')->getAuthorizationkey(
                $storeId
            )
        );
    }

    /**
     * check if response has all the required params
     *
     * @param array $params
     *
     * @return bool
     */
    private function checkResponseParams(array $params)
    {
        $result = true;
        $requiredKeys = $this->getRequiredParams();
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $params)) {
                $result = false;
            }
        }

        return $result;
    }


    /**
     * check if the order dependent params exist
     *
     * @param Mage_Sales_Model_Order $order
     * @param array                  $params
     *
     * @return bool
     */
    private function checkOrderDependentConditions(
        Mage_Sales_Model_Order $order, array $params
    ) {
        $result = true;
        $payment = $order->getPayment();
        if (!$this->validateNonPcExpercashPayments($payment)
            || !$this->getBarzahlenParameterExists($payment, $params)
            || !$this->validatePcExpercashPayment($payment, $order, $params)
        ) {
            $result = false;
        }
        return $result;
    }

    /**
     * check if the param paymenStatus exists if payment method was barzahlen
     *
     * @param $payment Mage_Sales_Model_Order_Payment
     * @param $params  array
     *
     * @return bool
     */
    private function getBarzahlenParameterExists($payment, $params)
    {
        $result = true;
        if ($payment->getMethod() == Expercash_Expercash_Model_Expercashpc::PAYMENT_METHOD_NAME
            && !array_key_exists('paymentStatus', $params)
        ) {
            $result = false;
        }
        return $result;
    }

    /**
     * check if notifyActionCounter is not above 1 for non barzahlen payment method
     *
     * @param $payment Mage_Sales_Model_Order_Payment
     *
     * @return bool
     */
    private function validateNonPcExpercashPayments($payment)
    {
        $result = true;
        if ($payment->getMethod() != Expercash_Expercash_Model_Expercashpc::PAYMENT_METHOD_NAME
            && $payment->getAdditionalInformation('notifyActionCounter') >= 1
        ) {
            $result = false;
        }
        return $result;
    }

    /**
     * If payment method was barzahlen and the paymenStatus is paid, the notifyCounter should be max 2
     *
     * @param $payment Mage_Sales_Model_Order_Payment
     *
     * @return bool
     */
    private function validatePcExpercashPayment($payment, $order, $params)
    {
        $result = true;
        if ($payment->getMethod() == Expercash_Expercash_Model_Expercashpc::PAYMENT_METHOD_NAME
            && $payment->getAdditionalInformation('paymentStatus')
            == Expercash_Expercash_Model_Expercashpc::BARZAHLEN_STATUS_PAID
            && !$this->validateBarzahlenSecondCall($order)
        ) {
            $result = false;
        }

        if ($payment->getMethod() == Expercash_Expercash_Model_Expercashpc::PAYMENT_METHOD_NAME
            && $payment->getAdditionalInformation('paymentStatus')
            == Expercash_Expercash_Model_Expercashpc::BARZAHLEN_STATUS_OPEN
            && $params['paymentStatus'] != Expercash_Expercash_Model_Expercashpc::BARZAHLEN_STATUS_PAID
        ) {
            $result = false;
        }
        return $result;
    }

    /**
     * check if the second call to the notify action was not older then 14 days past order creation date
     *
     * @param $order Mage_Sales_Model_Order
     *
     * @return bool
     */
    private function validateBarzahlenSecondCall($order)
    {
        $result = false;
        $orderDate = strtotime($order->getCreatedAt());
        $allowedDate = time();
        if (round(($allowedDate - $orderDate) / (3600 * 24)) <= 14) {
            $result = true;
        }
        return $result;
    }

    /**
     * get the required params
     *
     * @return array
     */
    private function getRequiredParams()
    {
        return $this->requiredParams;
    }

    /**
     * load and return the order based on increment id
     *
     * @param $orderIncrementId
     *
     * @return null|Mage_Sales_Model_Order
     */
    public function getOrder($orderIncrementId)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId(
            $orderIncrementId
        );
        if (null === $order->getId()) {
            Mage::throwException(
                $this->getHelper()->__('Could not load order.')
            );
        }

        return $order;
    }

    /**
     *  Save invoice for order
     *
     * @param    Mage_Sales_Model_Order $order
     *
     * @return   boolean Can save invoice or not
     */
    public function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            Mage::helper('expercash/data')->log(
                sprintf(
                    "Save invoice for order '%s'",
                    $order->getIncrementId()
                )
            );

            $invoice = $order->prepareInvoice();
            $invoice->register();
            $transaction = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            $this->sendInvoiceToCustomer($invoice);

            return true;
        }
        return false;
    }

    /**
     * set the expercash payment reference based on payment type
     *
     * @param $response  array
     * @param $expercash Mage_Sales_Model_Order_Payment
     * @param $order     Mage_Sales_Model_Order
     */
    public function setExpercashRef($response, $expercash, $order)
    {
        $storeId = $order->getStoreId();
        $expRefType = 'expercash_gutid';
        if (in_array($expercash->getConfigData('paymenttype', $storeId), $this->getBuyTypes())
        ) {
            $expRefType = 'expercash_epi_payment_id';
        }
        $expercash->setExperCashData(
            $order->getQuoteId(), $expRefType, $response["GuTID"]
        );
    }

    /**
     * return the payment types
     *
     * @return array
     */
    private function getBuyTypes()
    {
        return array(
            Expercash_Expercash_Model_Expercashcc::PAYMENT_TYPE_CC_BUY,
            Expercash_Expercash_Model_Expercashelv::PAYMENT_TYPE_ELV_BUY,
            Expercash_Expercash_Model_Expercashmp::PAYMENT_TYPE_MP_BUY,
            Expercash_Expercash_Model_Expercashmpf::PAYMENT_TYPE_MPF_BUY,
        );
    }

    /**
     * set the expercash payment data on the payment object
     *
     * @param $response  array
     * @param $expercash Mage_Sales_Model_Order_Payment
     * @param $order     Mage_Sales_Model_Order
     */
    public function setTxData($response, $expercash, $order)
    {
        $storeId = $order->getStoreId();
        $expercash->setExperCashData(
            $order->getQuoteId(),
            'expercash_request_type',
            $expercash->getConfigData('paymenttype', $storeId)
        );

        $expercash->setExperCashData(
            $order->getQuoteId(), 'expercash_transaction_id',
            $response["transactionId"]
        );
        $expercash->setExperCashData(
            $order->getQuoteId(), 'expercash_paymenttype',
            $response["paymentMethod"]
        );
    }

    /**
     * set payment status to payment
     *
     * @param $payment Mage_Sales_Model_Order_Payment
     */
    public function setPaymentStatus($payment)
    {
        $payment->setStatus(
            ExperCash_ExperCash_Model_ExperCash::STATUS_APPROVED
        );
        $payment->setStatusDescription(
            Mage::helper('expercash')->__('Successful.')
        );
    }

    /**
     * set the order status
     *
     * @param $response  array
     * @param $expercash Mage_Sales_Model_Order_Payment
     * @param $order     Mage_Sales_Model_Order
     */
    public function setOrderStatus($response, $expercash, $order)
    {
        $storeId = $order->getStoreId();
        $status = $expercash->getConfigData('order_status', $storeId);;
        $message = Mage::helper('expercash')->__(
            'Authorization was successful.'
        );
        if (in_array($expercash->getConfigData('paymenttype', $storeId), $this->getBuyTypes())
            || $this->isBarzahlenStatusPaid($response, $expercash, $storeId)
        ) {
            $message = Mage::helper('expercash')->__('Buy was successful.');
        } elseif ($expercash->getConfigData('paymenttype', $storeId)
            == Expercash_Expercash_Model_Expercashpc::PAYMENT_TYPE_PC
        ) {
            $status = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $message = Mage::helper('expercash')->__('Awaiting Barzahlen Payment.');

        }
        $order->addStatusToHistory(
            $status,
            $message
        );
    }

    /**
     * auto create invoice if config option is set to true
     *
     * @param $expercash Mage_Sales_Model_Order_Payment
     * @param $order     Mage_Sales_Model_Order
     */
    public function createInvoice($expercash, $order, $response)
    {
        $storeId = $order->getStoreId();
        if (($expercash->isDirectSaleEnabled() || $this->isBarzahlenStatusPaid($response, $expercash, $storeId))
            && $expercash->getConfigData('createinvoice', $storeId) == 1
        ) {
            if ($this->saveInvoice($order)) {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            }
        }
    }

    /**
     * cancel order and set history comment about it
     *
     * @param $order Mage_Sales_Model_Order
     */
    public function cancelOrder($order)
    {
        $order->cancel();
        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('expercash')->__(
                'Customer was rejected by ExperCash'
            )
        );
        $order->save();
    }

    /**
     * update the order with its payment and increase notifyActionCounter for each call
     *
     * @param $payment Mage_Sales_Model_Order_Payment
     * @param $order   Mage_Sales_Model_Order
     */
    public function updateOrder($payment, $order)
    {
        $notifyActionCounter = (int)$payment->getAdditionalInformation(
            'notifyActionCounter'
        );
        $payment->setAdditionalInformation(
            'notifyActionCounter', ++$notifyActionCounter
        );
        $order->setPayment($payment);
        $order->save();
    }

    /**
     * check if payment status for barzahlen is status paid
     *
     * @param $response  array
     * @param $expercash Mage_Sales_Model_Order_Payment
     * @param $storeId   int
     *
     * @return bool
     */
    private function isBarzahlenStatusPaid($response, $expercash, $storeId)
    {
        return $expercash->getConfigData('paymenttype', $storeId)
        == Expercash_Expercash_Model_Expercashpc::PAYMENT_TYPE_PC
        && $response['paymentStatus']
        == Expercash_Expercash_Model_Expercashpc::BARZAHLEN_STATUS_PAID;
    }


    public function saveTransactionData($payment, $responseArray, $transactionType)
    {
        $payment->setTransactionId($responseArray['taid']);
        foreach ($responseArray as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
    }

    public function getPaymentReferenceId($payment)
    {
        $result = $payment->getMethodInstance()->getExperCashInfo('expercash_gutid');
        if ($payment->getMethodInstance() instanceof Expercash_Expercash_Model_Expercashmpf) {
            $result = $payment->getAdditionalInformation('GuTID');
            $authTransAction = $payment->getAuthorizationTransaction();
            if ($authTransAction) {
                $result = $authTransAction->getTxnId();
            }

        }

        return $result;
    }

    /**
     * remove payment method from quote
     *
     * @param Mage_Sales_Model_Order $quote
     */
    public function removePaymentFromQuote(Mage_Sales_Model_Quote $quote)
    {
        $payment = $quote->getPayment();
        $payment->setMethod(null);
        $quote->setDataChanges(true);
        $quote->save();

    }
}
