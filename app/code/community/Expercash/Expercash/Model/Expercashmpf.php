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
class Expercash_Expercash_Model_Expercashmpf extends Expercash_Expercash_Model_Expercash
{
    const PAYMENT_TYPE_MPF_BUY = 'cc_buy';
    const PAYMENT_TYPE_MPF_AUTH = 'cc_authorize';
    const PAYMENT_TYPE_MPF_CAPTURE = 'cc_capture';
    const CODE = 'expercashmpf';

    /**
     * Availability options
     */
    protected $_code = self::CODE;
    protected $_paymentMethod = self::CODE;
    protected $_formBlockType = 'expercash/masterpass_fullcheckout_cc';
    protected $_infoBlockType = 'expercash/info_expercash';
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;

    public function isAvailable($quote = null)
    {
        $available = parent::isAvailable($quote);

        if (!$available) {
            return false;
        }
        if (in_array($quote->getQuoteCurrencyCode(), $this->getCurrenciesArray($quote->getStoreId()))) {
            return true;
        } else {
            return false;
        }
    }

    public function getCurrenciesArray($storeId = null)
    {
        return explode(',', $this->getConfigData('allowed_currency', $storeId));
    }

    /**
     * Map Magento payment action to Expercash payment action.
     *
     * @param Varien_Object $payment
     * @param int           $storeId
     *
     * @return null|string
     */
    public function getExpercashPaymentType($payment, $storeId)
    {
        $configType = $this->getConfigData('payment_action', $storeId);
        $expercashType = null;

        /* @var $payment Mage_Sales_Model_Order_Payment */
        if (Mage::app()->getStore()->isAdmin() && $payment->getAuthorizationTransaction()) {
            // backend invoice creation
            $expercashType = self::PAYMENT_TYPE_MPF_CAPTURE;
        } elseif ($configType === Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE) {
            // frontend authorize action
            $expercashType = self::PAYMENT_TYPE_MPF_AUTH;
        } elseif ($configType === Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE) {
            // frontend authorize_capture action
            $expercashType = self::PAYMENT_TYPE_MPF_BUY;
        }

        return $expercashType;
    }

    /**
     * capture the amount with transaction id
     *
     * @access public
     *
     * @param Varien_Object $payment
     * @param string        $amount
     *
     * @return void
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $paymentType = $this->getExpercashPaymentType(
            $payment,
            $this->getInfoInstance()->getOrder()->getStoreId()
        );

        try {
            Mage::getModel('expercash/api_masterpass_capture')->capture($payment, $amount, $paymentType);
        } catch (Exception $e) {
            // if its a capture while in frontend i.e. if payment method is configured as directsale
            // we have to remove payment method from quote in error case
            if (false === Mage::app()->getStore()->isAdmin()) {
                $onepage = Mage::getSingleton('checkout/type_onepage');
                $quote = $onepage->getQuote();
                $this->getPaymentHelper()->removePaymentFromQuote($quote);
                $this->getDataHelper()->log($e->getMessage());
                $onepage->getCheckout()->setGotoSection('payment');
                $onepage->getCheckout()->setUpdateSection('payment-method');
            }

            Mage::throwException(sprintf('%s', $this->getDataHelper()->__(self::PAYMENT_FAILED_MESSAGE)));
        }
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        $order = $this->getInfoInstance()->getOrder();
        try {
            $requestParams = Mage::getModel('expercash/request_masterpass_epi')->getPlaceOrderParams(
                $order,
                self::PAYMENT_TYPE_MPF_AUTH
            );
            $epiClient = Mage::getModel('expercash/api_masterpass_epi');
            $response = $epiClient->doEpiRequest($requestParams);
            $checkOrderHandling = Mage::helper('expercash/masterpass')->orderAndTransactionHandling($response, $order);

            if (!$checkOrderHandling) {
                Mage::throwException('Error occurred while trying to authorize.');
            }

        } catch (Exception $e) {
            // remove payment method from quote and save quote, so customer can choose another payment method
            // otherwise he is stuck in checkout in error case
            $onepage = Mage::getSingleton('checkout/type_onepage');
            $this->getPaymentHelper()->removePaymentFromQuote($order->getQuote());
            $this->getDataHelper()->log($e->getMessage());
            $onepage->getCheckout()->setGotoSection('payment');
            $onepage->getCheckout()->setUpdateSection('payment-method');
            
            Mage::throwException(sprintf('%s', $this->getDataHelper()->__(self::PAYMENT_FAILED_MESSAGE)));
        }
    }

    /***
     * getter for expercash payment helper
     *
     * @return Expercash_Expercash_Helper_Payment
     */
    protected function getPaymentHelper()
    {
        return Mage::helper('expercash/payment');
    }

    /**
     * use parent validate and add custom error message to session
     *
     * @overrides \Mage_Payment_Model_Method_Abstract::validate
     * @return Mage_Payment_Model_Abstract|void
     */
    public function validate()
    {
        try {
            parent::validate();
        } catch (Exception $e) {

            // incase of error remove payment method from quote
            $this->getPaymentHelper()->removePaymentFromQuote($this->getInfoInstance()->getQuote());
            Mage::getSingleton('core/session')->addError(
                Mage::helper('expercash/data')->__(
                    'Masterpass FullCheckout is not allowed for billing country. Please choose another payment method.'
                )
            );
        }

        return $this;
    }

    /**
     * Get redirect URL
     *
     * @return Mage_Payment_Helper_Data
     */
    public function getOrderPlaceRedirectUrl()
    {
        return null;
    }

    /**
     *
     *
     * @return bool
     */
    public function isDirectSaleEnabled()
    {
        return $this->getConfigData('payment_action') == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * set additional data on the payment
     *
     * @param Mage_Payment_Model_Info $payment
     * @param array                   $responseParams
     */
    public function setAdditionalPaymentInfo(Mage_Payment_Model_Info $payment, $responseParams)
    {
        $params = array(
            "currency"      => $payment->getQuote()->getQuoteCurrencyCode(),
            "paymentMethod" => $payment->getMethodInstance()->getCode(),
            "GuTID"         => $responseParams['GuTID'],
            "maskedPan"     => $responseParams['masked_pan'],
            "validThru"     => $responseParams['valid_thru'],
            "owner"         => $responseParams['owner'],
            "cardScheme"    => $responseParams['card_scheme'],
        );

        foreach ($params as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }
    }
}
