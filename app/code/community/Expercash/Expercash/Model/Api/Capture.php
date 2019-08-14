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
class Expercash_Expercash_Model_Api_Capture extends Expercash_Expercash_Model_Api_Abstract
{
    /**
     * Trigger the capture process of the Expercash gateway
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @param string $capture_type
     *
     * @return void
     */
    public function capture($payment, $amount, $capture_type)
    {
        $storeId  = $payment->getOrder()->getStoreId();
        if ($amount > 0) {
            $requestParameters = $this->getRequestParams($payment, $amount, $capture_type);
            $responseBody = $this->_postRequest($requestParameters, $storeId);
            $this->validateAndSaveResponse($responseBody, $payment);

            $parsedXML = $this->parseResponse($responseBody);
            $responseArray = Mage::helper('core/data')->xmlToAssoc($parsedXML);

            Mage::helper('expercash/payment')->saveTransactionData(
                $payment,
                $responseArray,
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE
            );
        }
    }

    protected function getRequestParams($payment, $amount, $captureType)
    {
        $storeId  = $payment->getOrder()->getStoreId();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        /** @var $config Expercash_Expercash_Model_Config */
        $config   = Mage::getModel('expercash/config');

        $requestParameters = array(
            "pid"       => $config->getProjectId($storeId),
            "pkey"      => $config->getGatewayKey($storeId),
            "cref"      => $order->getIncrementId(),
            "amount"    => round($amount * 100),
            "action"    => $captureType,
            "reference" => Mage::helper('expercash/payment')->getPaymentReferenceId($payment),
        );

        return $requestParameters;
    }

    /**
     * Check the response for validity and save the reponse data
     *
     * @param string $response
     * @param Mage_Sales_Model_Payment $payment
     * @return void
     * @throws Mage_Core_Exception
     */
    public function validateAndSaveResponse($response, $payment)
    {
        try {
            $xmlResponse = $this->parseResponse($response);

            if ($xmlResponse->rc != 100) {
                Mage::throwException(
                    Mage::helper('expercash/data')->__('Response status is not correct: %s', $xmlResponse->rc)
                );
            }

            $payment->setStatus(Expercash_Expercash_Model_Expercash::STATUS_SUCCESS);
            $this->saveExperCashData($payment, $xmlResponse);
            $this->saveOrderHistory($payment);
            $payment->getOrder()->save();
        } catch (Exception $e) {
            //Reload order to avoid that the order items were marked as invoiced
            $order = Mage::getModel("sales/order")->load(
                $payment->getOrder()->getId()
            );

            $order->addStatusToHistory(
                $order->getStatus(),
                Mage::helper('expercash/data')->__('Capture-attempt failed with message: %s', $e->getMessage())
            );
            $order->save();

            Mage::throwException(
                Mage::helper('expercash/data')->__('Error during capture: %s', $e->getMessage())
            );
        }
    }

    /**
     * saves the expercash order history
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return void
     */
    public function saveOrderHistory($payment)
    {
        $payment->setStatusDescription(
            Mage::helper('expercash/data')->__(
                'Capture was successful. Captured amount: %s %s',
                $payment->getAmount(),
                $payment->getOrder()->getOrderCurrencyCode()
            )
        );

        $payment->getOrder()->addStatusToHistory(
            $payment->getOrder()->getStatus(),
            Mage::helper('expercash/data')->__('Capture was successful.')
        );
    }

    /**
     * saves the expercash response in Sales_Flat_Quote_Payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param SimpleXMLElement $response
     * @return void
     */
    public function saveExperCashData($payment, $response)
    {
        $paymentHelper = Mage::helper('expercash/payment');
        $paymentHelper->setExperCashData(
            $payment->getOrder()->getQuoteId(),
            'expercash_gutid_capture',
            $response->taid
        );

        $paymentHelper->setExperCashData(
            $payment->getOrder()->getQuoteId(),
            'expercash_epi_payment_id',
            $response->epi_payment_id
        );
    }
}
