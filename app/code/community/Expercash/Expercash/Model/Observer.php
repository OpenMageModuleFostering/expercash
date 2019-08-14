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

class Expercash_Expercash_Model_Observer
{

    /**
     * set step data for masterpass fullcheckout
     *
     * @param Varien_Event_Observer $observer
     */
    public function setStepData(Varien_Event_Observer $observer)
    {
        $checkout = Mage::getModel('checkout/session');

        if (!$checkout->getQuote()->getPayment()->getMethod()) {
            return;
        }

        $paymentMethod = $checkout->getQuote()->getPayment()->getMethodInstance();


        if ($paymentMethod instanceof Expercash_Expercash_Model_Expercashmpf) {
            $checkout
                ->setStepData('login', 'allow', true)
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true)
                ->setStepData('shipping', 'complete', true);
        }
    }


    /**
     * Disable MasterPass Full Checkout in case it was not already set via API
     * calls. Disable all other payment methods in case MasterPass Full Checkout
     * was already selected via API calls.
     *
     * @see Expercash_Expercash_Helper_Masterpass::setPaymentInfoToQuote()
     *
     * @param Varien_Event_Observer $observer
     */
    public function setPaymentAvailable(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        $checkResult = $observer->getResult();
        $checkoutPaymentMethod = $observer->getMethodInstance();

        if (!$quote instanceof Mage_Sales_Model_Quote) {
            return;
        }

        if (!$checkResult->isAvailable) {
            return;
        }

        $quotePaymentMethod = $observer->getQuote()->getPayment()->getMethod();
        $mpfCode = Mage::getModel('expercash/expercashmpf')->getCode();

        // Full checkout should not be available if it was not set yet.
        if (($checkoutPaymentMethod instanceof Expercash_Expercash_Model_Expercashmpf)
            && ($quotePaymentMethod !== $mpfCode)
        ) {
            $checkResult->isAvailable = false;
        }

        // Other payment methods should not be available if Full Checkout is already set.
        if ((!$checkoutPaymentMethod instanceof Expercash_Expercash_Model_Expercashmpf)
            && ($quotePaymentMethod === Mage::getModel('expercash/expercashmpf')->getCode())
        ) {
            $checkResult->isAvailable = false;
        }
    }

    /***
     *
     * Validate Masterpass session before place order process starts.
     * Validations fails if session lifetime is exceed or customer changed billing address.
     *
     * @event sales_order_place_before
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function validateMasterpassSession(Varien_Event_Observer $observer)
    {
        /** @var  $quote Mage_Sales_Model_Quote */
        $quote = $observer->getOrder()->getQuote();
        $methodInstance = $quote->getPayment()->getMethodInstance();

        if ($methodInstance instanceof Expercash_Expercash_Model_Expercashmpf) {

            try {

                Mage::helper('expercash/masterpass')->validMasterpassSession($quote);

            } catch (Exception $e) {
                Mage::helper('expercash/masterpass')->clearMasterpassSessionData();
                // clear payment method
                $this->getPaymentHelper()->removePaymentFromQuote($quote);
                $this->getOnepage()->getCheckout()->setGotoSection('login');
                Mage::throwException(
                    Mage::helper('expercash/data')->__(
                        'Session timed out or session data invalid. ' .
                        'Either checkout with Masterpass Full Checkout again or use another payment method.'
                    )
                );
            }
        }

        return $this;
    }

    /**
     * Invalidate masterpass session and remove payment method from quote
     * if payment method is masterpass fullcheckout and customer goes to cart
     * after he already was in checkout process
     *
     * @event checkout_cart_save_after
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function invalidateMasterpassSession(Varien_Event_Observer $observer)
    {
        $quote = $observer->getCart()->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();

        if ($paymentMethod === Expercash_Expercash_Model_Expercashmpf::CODE) {
            // clear payment method
            $this->getPaymentHelper()->removePaymentFromQuote($quote);
            Mage::helper('expercash/masterpass')->clearMasterpassSessionData();

        }

        return $this;
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
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

}
