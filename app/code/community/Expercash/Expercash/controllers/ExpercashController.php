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
class Expercash_Expercash_ExpercashController extends Mage_Core_Controller_Front_Action
{


    private $paymentHelper = null;

    public function setPaymentHelper($paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @return Expercash_Expercash_Helper_Payment
     */
    public function getPaymentHelper()
    {
        if (null === $this->paymentHelper) {
            $this->paymentHelper = Mage::helper('expercash/payment');
        }
        return $this->paymentHelper;
    }

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * when customer select ExperCash payment method
     */
    public function indexAction()
    {
        if (!$session = $this->getCheckout()) {
            $this->_redirect('checkout/cart/');
            return;
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('expercash');
        $this->renderLayout();
    }

    /**
     * ExperCash
     */
    public function resetAction()
    {
        if (!$session = $this->getCheckout()) {
            $this->_redirect('checkout/cart/');
            return;
        }
        //Load Order
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());

        //Reset Status of Order once
        $order->addStatusToHistory(
            Mage_Sales_Model_Order::STATE_HOLDED,
            Mage::helper('expercash')->__('ExperCash payment loaded')
        );
        $order->save();

        Mage::helper('expercash/data')->log(
            sprintf(
                "Order '%s' was resetted by ExperCash Request",
                $order->getIncrementId()
            )
        );

        //Redirect to indexAction
        $this->_redirect('expercash/expercash/');
    }

    /**
     * ExperCash returns POST variables to this action
     */
    public function successAction()
    {
        $session = $this->getCheckout();
        $session->unsExpercashOrderId();
        $session->unsExpercashRealOrderId();
        $session->setQuoteId($session->getExpercashQuoteId(true));
        $session->getQuote()->setIsActive(false)->save();

        Mage::helper('expercash/data')->log(
            sprintf(
                "User arrived on successAction with params:\n%s",
                Zend_Json::encode($this->getRequest()->getParams())
            )
        );
        $this->_redirect('checkout/onepage/success');
    }

    /**
     * ExperCash returns POST variables to this action
     */
    public function notifyAction()
    {
        Mage::helper('expercash/data')->log(
            sprintf(
                "notifyAction was called with params:\n%s",
                Zend_Json::encode($this->getRequest()->getParams())
            )
        );
        $response = $this->getRequest()->getParams();
        $status   = $this->_checkReturnedData($response);

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($response['transactionId']);
        if ($status) {
            $session = $this->getCheckout();
            $session->unsExpercashOrderId();
            $session->unsExpercashRealOrderId();
            $session->setQuoteId($session->getExpercashQuoteId(true));
            $session->getQuote()->setIsActive(false)->save();
        }

    }

    /**
     * on error expcerash calls this action
     * refill cart on error
     */
    public function errorAction()
    {
        //Load Checkout session
        if (!$session = $this->getCheckout()) {
            $this->_redirect('checkout/cart/');
            return;
        }

        //Load Order
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());

        //Refill the cart with the same items like for the canceled order
        Mage::helper('expercash/data')->refillCart($order);

        $this->loadLayout();
        $this->getLayout()->getBlock('expercash');
        $this->renderLayout();
    }

    /**
     * Checking Get variables.
     *
     */
    protected function _checkReturnedData($response)
    {
        $status = false;
        if (!$this->getRequest()->isGet()) {
            $this->norouteAction();
            return;
        }
        $status = $this->getPaymentHelper()->checkReturnedData($response);
        return $status;
    }
}