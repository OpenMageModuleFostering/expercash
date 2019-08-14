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
class Expercash_Expercash_Model_Request_Iframe
{

    protected $order = null;

    /**
     * get the params for the token
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function getIframeParams(Mage_Sales_Model_Order $order)
    {
        $iFrameParams = array(
            'preparedSession' => $this->getTokenSessionId(),
            'sessionKey'      => $this->buildSessionKeyHash($order)
        );
        return $iFrameParams;
    }

    /**
     * build the hash for the sessionKeyParam
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function buildSessionKeyHash(Mage_Sales_Model_Order $order)
    {
        $sessionKeyHash = sha1(
            $this->getTokenSessionId()
            . $this->getJobId($order)
            . $this->getConfig()->getAuthorizationkey($order->getStoreId())
        );
        return $sessionKeyHash;
    }

    /**
     * get the expercash job id which is the order increment id
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    protected function getJobId(Mage_Sales_Model_Order $order)
    {
        return $order->getIncrementId();
    }

    /**
     * return the saved token session id
     *
     * @return string
     */
    protected function getTokenSessionId()
    {
        return $this->getCheckoutSession()->getData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY);
    }

    /**
     * getter for config model
     *
     * @return Expercash_Expercash_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('expercash/config');
    }

    /**
     * get the order from checkout
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $session = $this->getCheckoutSession();
        if (null == $this->order) {
            $this->order = Mage::getModel('sales/order')->load($session->getLastOrderId());
        }
        return $this->order;
    }

    /**
     * get store id from order
     *
     * @return string
     */
    protected function getStoreId()
    {
        return $this->getOrder()->getStoreId();
    }

    /**
     * get the checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * set the order and quote data to the checkout session
     *
     * @param $paymentObject
     */
    public function setOrderDataToSession()
    {
        $paymentObject = $this->getOrder()->getPayment()->getMethodInstance();
        $session       = $this->getCheckoutSession();
        $session->setExpercashOrderId($paymentObject->createExperCashOrderId());
        $session->setExpercashRealOrderId($session->getLastRealOrderId());
        $session->setExpercashQuoteId($session->getQuoteId());
    }

    /**
     * return the generated iframe url and unset the session data
     *
     * @return string
     */
    public function getIframeUrl()
    {
        $iframeParams  = $this->getIframeParams($this->getOrder());
        $iframeUrl     = $this->getDataHelper()->addRequestParam($this->getConfig()->getIframeUrl(), $iframeParams);
        $this->getCheckoutSession()->unsetData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY);

        return $iframeUrl;
    }

    /**
     * get the mage core url helper
     *
     * @return Mage_Core_Helper_Url
     */
    protected function getCoreUrlHelper()
    {
        return Mage::helper('core/url');
    }

    protected function getDataHelper()
    {
        return Mage::helper('expercash/data');
    }


} 