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

class Expercash_Expercash_Model_Expercash extends Mage_Payment_Model_Method_Abstract
{

    /**
     * the payment failed message string
     */
    const PAYMENT_FAILED_MESSAGE = "Please select another payment method!";

    /**
     * Availability options
     */
    protected $_code = 'expercash';
    protected $_paymentMethod = 'expercash';
    protected $_formBlockType = 'expercash/form_expercash';
    protected $_infoBlockType = 'expercash/info_expercash';
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_expercashStack = false;


    /**
     * authorize payment
     *
     * get token from expercash and save it in session
     * if something goes wrong jump to section before payment in checkout
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @throws Exception
     * @throws Mage_Checkout_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        parent::authorize($payment, $amount);
        $order   = $this->getInfoInstance()->getOrder();
        $section = $this->getSection($order);

        try {
            $requestParams = Mage::getModel('expercash/request_token_iframe')->getTokenParams($order);

            $token = Mage::getModel('expercash/api_api')->getIframeToken($requestParams);
            if (!$token) {
                throw new Mage_Checkout_Exception('No Expercash token.');
            }
            $this->getCheckoutSession()->setData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY, $token);

            $payment->setTransactionId($requestParams['transactionId']);
            $payment->setIsTransactionClosed(false);


        }
        catch (Mage_Checkout_Exception $ce) {
            $this->getDataHelper()->log($ce->getMessage());
            Mage::getSingleton('checkout/type_onepage')->getCheckout()->setGotoSection($section);
            throw $ce;
        }
        catch (Exception $e) {
            $this->getDataHelper()->log($e->getMessage());
            Mage::getSingleton('checkout/type_onepage')->getCheckout()->setGotoSection($section);
            Mage::throwException(sprintf('%s', $this->getDataHelper()->__(self::PAYMENT_FAILED_MESSAGE)));
        }

    }

    /**
     * get the expercash data helper
     *
     * @return Expercash_Expercash_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('expercash/data');
    }

    /**
     * Get redirect URL
     *
     * @return Mage_Payment_Helper_Data
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('expercash/expercash/reset');
    }

    /**
     * Calc Amount
     *
     * @return amount
     */
    protected function calcCentAmount($amount)
    {
        return number_format($amount * 100, 0, '.', '');
    }

    /**
     * refund the amount with transaction id
     *
     * @access public
     *
     * @param string $payment Varien_Object object
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $payment->setStatusDescription(Mage::helper('expercash')->__('Error in refunding the payment'));
        return $this;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param   string   $field
     * @param   null|int $storeId
     *
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return Mage::getModel('expercash/config')->getConfigData($field, $this->getCode(), $storeId);
    }

    /**
     *
     * @param integer $id QuoteId
     * @param string  $key
     * @param string  $value
     */
    public function setExperCashData($id, $key, $value)
    {
        Mage::helper('expercash/payment')->setExperCashData($id, $key, $value);
    }

    /**
     * wrapps the payment helper function getExperCashInfo
     *
     * @return string
     */
    public function createExperCashOrderId()
    {
        return Mage::helper('expercash/payment')->createExperCashOrderId();
    }

    /**
     * wrapps the payment helper function getExperCashInfo
     *
     * @param type $string
     *
     * @return string
     */
    public function getExperCashInfo($string)
    {
        $id = $this->getInfoInstance()->getOrder()->getQuoteId();
        return Mage::helper('expercash/payment')->getExperCashInfo($string, $id);
    }

    /**
     * set additional data on the payment
     *
     * @param Mage_Payment_Model_Info $payment
     * @param array                   $responseParams
     */
    public function setAdditionalPaymentInfo(Mage_Payment_Model_Info $payment, $responseParams)
    {
        foreach (Mage::helper('expercash/data')->getAdditionalPaymentParams() as $key) {
            if (array_key_exists($key, $responseParams)) {
                $payment->setAdditionalInformation($key, $responseParams[$key]);
            }
        }
    }

    /**
     * get the checkout section
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function getSection($order)
    {
        $section = 'shipping_method';
        if (false === ($order->getShippingAddress() instanceof Mage_Sales_Model_Order_Address)
            || 0 == strlen($order->getShippingAddress()->getPostcode())
        ) {
            $section = 'billing';
        }
        return $section;
    }

    /**
     * getter for the checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return bool
     */
    public function isDirectSaleEnabled()
    {
        return false;
    }
}