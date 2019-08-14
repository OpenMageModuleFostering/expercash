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
class Expercash_Expercash_FullcheckoutController extends Mage_Core_Controller_Front_Action
{


    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function startAction()
    {
        $quote = $this->getCheckout()->getQuote();
        $quote->reserveOrderId()->save();
        $quote->collectTotals();

        $requestParams = Mage::getModel('expercash/request_masterpass_epi')->getEpiParams($quote);
        $epiClient = Mage::getModel('expercash/api_masterpass_epi');


        try {
            $response = $epiClient->doEpiRequest($requestParams);

            // validate response rc
            $epiClient->validateResponse($response);

            // save taid and response timestamp to session
            $this->getMasterpassHelper()->saveDataToMasterpassSession('taid', $response['taid']);
            $this->getMasterpassHelper()->saveDataToMasterpassSession('timestamp', time());

            $redirectUrl = $response['masterpass_sign_in_url'];

        } catch (Exception $e) {
            Mage::getModel('core/session')->addError(
                Mage::helper('expercash/data')->__(
                    'Invalid response from Expercash. Please use default checkout.'
                )
            );
            Mage::helper('expercash/data')->log($e->getMessage());
            $redirectUrl = $this->_getRefererUrl();
        }


        // redirect to provided masterpass url
        $this->_redirectUrl($redirectUrl);


    }


    public function successAction()
    {
        $params = $this->getRequest()->getParams();
        $mpHelper = $this->getMasterpassHelper();

        try {

            $mpHelper->validateParams($params);
            $mpHelper->validateAndSaveGutId($params);
            $mpHelper->setBillingDataToQuoteAndSession($params);
            $mpHelper->setShippingDataToQuote($params);
            $mpHelper->initOnePageCheckout();
            $mpHelper->saveEpiResponseOnPayment($params);

        } catch (Exception $e) {

            Mage::helper('expercash/data')->log($e->getMessage());
        }

        $this->_redirect('checkout/onepage/index/');

    }

    public function errorAction()
    {
        //Load Checkout session
        if (!$session = $this->getCheckout()) {
            $this->_redirect('checkout/cart/');
            return;
        }

        Mage::getModel('core/session')->addError(
            Mage::helper('expercash/data')->__(
                'Operation canceled.'
            )
        );

        //Load Order
        $order = Mage::getModel('sales/order');

        $order->loadByIncrementId($session->getLastRealOrderId());

        //Refill the cart with the same items like for the canceled order
        Mage::helper('expercash/data')->refillCart($order);

        $this->_redirect('checkout/cart/');
    }


    /**
     * @return Expercash_Expercash_Model_Masterpass_Config
     */
    protected function getMasterpassConfig()
    {
        return Mage::getModel('expercash/masterpass_config');
    }


    /**
     * @return Expercash_Expercash_Helper_Masterpass
     */
    protected function getMasterpassHelper()
    {
        return Mage::helper('expercash/masterpass');
    }

}