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
class Expercash_Expercash_Helper_Masterpass extends Expercash_Expercash_Helper_Payment
{
    const MASTERPASS_SESSION_KEY = 'masterpass';

    const SESSION_LIFETIME_MAX = 3600;


    /**
     * Save MasterPass data to customer session.
     *
     * @param string $key
     * @param string $value
     */
    public function saveDataToMasterpassSession($key, $value)
    {
        $sessionData = $this->getCustomerSession()->getData(self::MASTERPASS_SESSION_KEY);
        $sessionData[$key] = $value;
        $this->getCustomerSession()->setData(self::MASTERPASS_SESSION_KEY, $sessionData);
    }

    /**
     * Check if MasterPass session is not yet timed out.
     *
     * @param string[] $sessionData
     *
     * @return bool
     */
    protected function isSessionLifeTimeValid($sessionData)
    {
        $result = false;
        if (is_array($sessionData) && isset($sessionData['timestamp'])) {
            $initialTimeStamp = $sessionData['timestamp'];
            $now = time();
            $timeDiff = $now - $initialTimeStamp;
            if ($timeDiff <= self::SESSION_LIFETIME_MAX) {
                $result = true;
            }
        }

        return $result;
    }


    /**
     * Check if MasterPass session is valid.
     *
     * @param string[]               $sessionData
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function isSessionValid($sessionData, Mage_Sales_Model_Quote $quote)
    {
        $result = false;
        if ($this->isSessionLifeTimeValid($sessionData)
            && $this->isBillingAddressValid($sessionData, $quote)
        ) {
            $result = true;
        }

        return $result;
    }


    /**
     * Check if current billing address is unchanged.
     *
     * @param string[]               $sessionData
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    protected function isBillingAddressValid($sessionData, Mage_Sales_Model_Quote $quote)
    {
        $result = false;
        if (is_array($sessionData) && isset($sessionData['billingAddress'])) {
            $quoteBillingAddressArray = $this->getBillingArray($quote->getBillingAddress());
            $quoteBillingAddressHash = $this->getArrayHash($quoteBillingAddressArray);
            if ($sessionData['billingAddress'] === $quoteBillingAddressHash) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Clear all MasterPass related data from customer session.
     *
     * @return void
     */
    public function clearMasterpassSessionData()
    {
        $customerSession = $this->getCustomerSession();
        $customerSession->unsetData(self::MASTERPASS_SESSION_KEY);
    }

    /**
     * Init session validation.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return string[] Current session data if still valid.
     * @throws Mage_Core_Exception
     */
    public function validMasterpassSession(Mage_Sales_Model_Quote $quote)
    {
        $masterPassSessionData = $this->getCustomerSession()->getData(self::MASTERPASS_SESSION_KEY);
        if (!$this->isSessionValid($masterPassSessionData, $quote)) {
            Mage::throwException('Session data not valid or session timed out');
        }

        return $masterPassSessionData;
    }

    /**
     * Retrieve customer session object.
     *
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return Mage::getModel('customer/session');
    }

    /**
     * Validate notificationSignature query param when returning from MasterPass wallet.
     *
     * @param string[] $params
     *
     * @throws Mage_Core_Exception
     */
    public function validateParams($params)
    {
        $notificationSignature = null;

        if (isset($params['notificationSignature'])) {
            $notificationSignature = $params['notificationSignature'];
            unset($params['notificationSignature']);
        }

        $queryParams = http_build_query($params, '', '&');

        $computed = md5($queryParams . $this->getAuthorizationKey());

        if ($computed !== $notificationSignature) {
            Mage::throwException('Validate params: Signature and build hash do not match!');
        }
    }

    /**
     * Validate GuTID2 query param when returning from MasterPass wallet.
     *
     * @param $params
     *
     * @throws Mage_Core_Exception
     */
    public function validateAndSaveGutId($params)
    {
        $guTID2 = null;
        $guTID2Hash = null;

        if (isset($params['GuTID2']) && isset($params['GuTID2Hash'])) {
            $guTID2 = $params['GuTID2'];
            $guTID2Hash = $params['GuTID2Hash'];
        }

        $calculatedGutid2Hash = md5($this->getAuthorizationKey() . $guTID2);

        if ($guTID2Hash != $calculatedGutid2Hash) {
            Mage::throwException('Validate GutId: GutId2Hash and build hash do not match!');
        }

        //save billing in masterpass session
        $this->saveDataToMasterpassSession('GuTID2', $params['GuTID2']);

    }

    /**
     * Obtain MasterPass config model.
     *
     * @return Expercash_Expercash_Model_Masterpass_Config
     */
    protected function getMasterpassConfig()
    {
        return Mage::getModel('expercash/masterpass_config');
    }

    /**
     * Obtain pkey (GatewayKey) from configuration.
     *
     * @return string
     */
    protected function getAuthorizationKey()
    {
        $storeId = Mage::app()->getStore()->getId();
        return $this->getMasterpassConfig()->getAuthorizationkey($storeId);
    }

    /**
     * Save billing data to quote when returning from MasterPass wallet.
     *
     * @param string[] $params
     */
    public function setBillingDataToQuoteAndSession($params)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $params = new Varien_Object($params);

        $billingAddress->setStreetFull($params->getData('customer_address1'));
        $billingAddress->setCity($params->getData('customer_city'));
        $billingAddress->setCountryId($params->getData('customer_country'));
        $billingAddress->setRegion($params->getData('customer_country_subdivision'));
        $region = Mage::getModel('expercash/directory_region')->loadByName(
            $billingAddress->getRegion(), $billingAddress->getCountryId()
        );

        $billingAddress->setRegionId($region->getId());
        $billingAddress->setEmail($params->getData('customer_email'));
        $billingAddress->setFirstname($params->getData('customer_prename'));
        $billingAddress->setLastname($params->getData('customer_name'));
        $billingAddress->setTelephone($params->getData('customer_telephone'));
        $billingAddress->setPostcode($params->getData('customer_zip'));
        $billingAddress->save();


        $billingAddressHash = $this->getArrayHash($this->getBillingArray($billingAddress));
        $this->saveDataToMasterpassSession('billingAddress', $billingAddressHash);
    }

    /**
     * Calculate hash from given data.
     *
     * @param mixed $dataArray
     *
     * @return string
     */
    public function getArrayHash($dataArray)
    {
        return md5(json_encode($dataArray));
    }

    /**
     * Obtain billing address properties as array.
     *
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     *
     * @return string[]
     */
    public function getBillingArray($billingAddress)
    {
        $billingAddressArray = $billingAddress->toArray(
            array(
                'street',
                'city',
                'country_id',
                'region',
                'region_id',
                'email',
                'firstname',
                'lastname',
                'telephone',
                'postcode',
            )
        );

        unset($billingAddressArray['items']);
        unset($billingAddressArray['totals']);
        unset($billingAddressArray['rates']);

        return $billingAddressArray['email'];
    }

    /**
     * Save shipping data on quote.
     *
     * @param mixed[] $params
     *
     * @return void
     */
    public function setShippingDataToQuote($params)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $params = new Varien_Object($params);

        // master data
        $deliveryFirstname = '';
        $deliveryLastname = '';
        $deliveryFullname = $params->getData('delivery_fullname');
        if ($deliveryFullname) {
            $deliveryFullname = explode(' ', $deliveryFullname);
            $deliveryLastname = array_pop($deliveryFullname);
            $deliveryFirstname = empty($deliveryFullname) ? '' : implode($deliveryFullname);
        }
        $shippingAddress->setFirstname($deliveryFirstname);
        $shippingAddress->setLastname($deliveryLastname);
        $shippingAddress->setTelephone($params->getData('delivery_telephone'));

        // address data
        $shippingAddress->setStreetFull($params->getData('delivery_address1'));
        $shippingAddress->setCity($params->getData('delivery_city'));
        $shippingAddress->setCountryId($params->getData('delivery_country'));
        $shippingAddress->setRegion($params->getData('delivery_country_subdivision'));
        $region = Mage::getModel('expercash/directory_region')->loadByName(
            $shippingAddress->getRegion(), $shippingAddress->getCountryId()
        );
        $shippingAddress->setRegionId($region->getId());
        $shippingAddress->setPostcode($params->getData('delivery_zip'));

        $shippingAddress->save();
    }

    /**
     * Set MasterPass Full Checkout payment method in quote.
     *
     * @return void
     */
    public function setPaymentInfoToQuote()
    {
        $expercashCode = Mage::getModel('expercash/expercashmpf')->getCode();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($expercashCode);
        } else {
            $quote->getShippingAddress()->setPaymentMethod($expercashCode);
        }

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $payment = $quote->getPayment();
        $payment->importData(array('method' => $expercashCode));
        $quote->save();
    }

    /**
     * Set guest method and selected payment method to onepage checkout.
     *
     * @return void
     */
    public function initOnePageCheckout()
    {
        $this->setPaymentInfoToQuote();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Obtain current onepage checkout object.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnePage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Save query params to payment's additional information when returning from MasterPass wallet.
     *
     * @param string[] $params
     *
     * @return bool
     */
    public function saveEpiResponseOnPayment($params)
    {
        $quote = $this->getOnePage()->getQuote();
        $payment = $quote->getPayment()->getMethodInstance();
        //Save additional payment information to order-payment
        $payment->setAdditionalPaymentInfo($quote->getPayment(), $params);
        $quote->save();
    }

    /**
     * Check and handle MasterPass Full ChecKout API response data.
     *
     * @param string[]               $response
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function orderAndTransactionHandling($response, $order)
    {
        $storeId = $order->getStoreId();
        $payment = $order->getPayment();

        if (is_array($response) && isset($response['rc']) && $response['rc'] == '100') {
            /* @var $expercash Expercash_Expercash_Model_Expercash */
            $expercash = $order->getPayment()->getMethodInstance();

            $this->setExpercashRef($response, $expercash, $order);
            $this->setTxData($response, $expercash, $order, $storeId);
            $this->setPaymentStatus($payment);

            $payment->setAdditionalInformation('transactionId', $response['taid']);
            $payment->setTransactionId($response['taid']);
            $payment->setIsTransactionClosed(false);

            //Save additional payment information to order-payment
            $order->addStatusHistoryComment(
                $response['rctext'],
                $payment->getConfigData('order_status', $storeId)
            );

            if (false == $order->getEmailSent()) {
                $order->sendNewOrderEmail();
            }

            $status = true;
            $this->updateOrder($payment, $order);

        } else {
            $this->cancelOrder($order);
            $status = false;
        }

        $order->save();

        return $status;
    }

    /**
     * Save transaction reference to quote payment:
     * - sales_flat_quote_payment.expercash_gutid
     *
     * @param string[]                               $response
     * @param Expercash_Expercash_Model_Expercashmpf $expercash
     * @param Mage_Sales_Model_Order                 $order
     */
    public function setExpercashRef($response, $expercash, $order)
    {
        $expRefType = 'expercash_gutid';
        $expercash->setExperCashData(
            $order->getQuoteId(), $expRefType, $response["taid"]
        );
    }

    /**
     * Save transaction info to quote payment:
     * - sales_flat_quote_payment.expercash_request_type
     * - sales_flat_quote_payment.expercash_transaction_id
     * - sales_flat_quote_payment.expercash_paymenttype
     *
     * @param string[]                               $response
     * @param Expercash_Expercash_Model_Expercashmpf $expercash
     * @param Mage_Sales_Model_Order                 $order
     */
    public function setTxData($response, $expercash, $order)
    {
        if ($expercash instanceof Expercash_Expercash_Model_Expercashmpf) {
            $expPaymentType = $expercash->getExpercashPaymentType($order->getPayment(), $order->getStoreId());
            $expercash->setExperCashData($order->getQuoteId(), 'expercash_request_type', $expPaymentType);
        }

        $expercash->setExperCashData($order->getQuoteId(), 'expercash_transaction_id', $response["taid"]);
        $expercash->setExperCashData($order->getQuoteId(), 'expercash_paymenttype', 'CC');
    }

    /**
     * Check if MasterPass Full Checkout is currently selected as payment method.
     *
     * @return bool
     */
    public function isFullCheckout()
    {
        $payment = $this->getOnePage()->getQuote()->getPayment();
        if (!$payment->getMethod()) {
            return false;
        }

        if (!$payment->getMethodInstance() instanceof Expercash_Expercash_Model_Expercashmpf) {
            return false;
        }

        return true;
    }

    /**
     * Obtain the frontend template to be used in one page checkout.
     *
     * In case MasterPass Full Checkout is selected as payment method, return
     * a customized template including additional JS, Otherwise, return the
     * template as defined via layout xml files.
     *
     * @return string
     */
    public function getOpcTemplate()
    {
        $template = Mage::getSingleton('core/layout')->getBlock('checkout.onepage')->getTemplate();

        if ($this->isFullCheckout()) {
            $template = 'expercash/checkout/onepage.phtml';
        }

        if ($this->isCE15()) {
            $template = 'expercash/checkout/ce15/onepage.phtml';
        }

        return $template;
    }

    protected function isCE15()
    {
        $result = false;
        if (Mage::getVersion() == '1.5.0.1') {
            $result = true;
        }

        return $result;
    }


}
