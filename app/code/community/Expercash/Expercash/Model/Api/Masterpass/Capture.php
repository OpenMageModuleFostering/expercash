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
class Expercash_Expercash_Model_Api_Masterpass_Capture extends Expercash_Expercash_Model_Api_Capture
{
    /**
     * Trigger the capture process of the Expercash gateway
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float                          $amount
     * @param string                         $capture_type
     *
     * @return void
     */
    public function capture($payment, $amount, $capture_type)
    {

        $storeId = $payment->getOrder()->getStoreId();
        if ($amount > 0) {
            $requestParameters = $this->getRequestParams($payment, $amount, $capture_type);
            $responseBody = $this->_postRequest($requestParameters, $storeId);
            $this->validateAndSaveResponse($responseBody, $payment);

            $xmlResponse = $this->parseResponse($responseBody);
            $responseArray = Mage::helper('core/data')->xmlToAssoc($xmlResponse);

            Mage::helper('expercash/payment')->saveTransactionData(
                $payment,
                $responseArray,
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE
            );

            $checkOrderHandling = Mage::helper('expercash/masterpass')->orderAndTransactionHandling(
                $responseArray,
                $payment->getOrder()
            );

            if (false === $checkOrderHandling) {
                Mage::throwException('Error occurred while trying to capture.');
            }
        }
    }

    /**
     * get params for capture request
     *
     * @param $payment
     * @param $amount
     * @param $captureType
     *
     * @return array
     */
    protected function getRequestParams($payment, $amount, $captureType)
    {
        $requestParameters = Mage::getModel('expercash/request_masterpass_epi')->getPlaceOrderParams(
            $payment->getOrder(),
            $captureType
        );

        return $requestParameters;
    }


}
