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

class Expercash_Expercash_Model_Request_Token
{
    /**
     * default locale string
     */
    const DEFAULT_LOCALE = 'en';

    /**
     * the locale codes that are supported by expercash
     *
     * @var array
     */
    protected $_supportedLocales = array('en', 'de', 'fr', 'es', 'it', 'du');

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function getTokenParams($order)
    {
        $config = $this->getConfig();
        $paymentObject = $order->getPayment()->getMethodInstance();
        $code = $paymentObject->getCode();

        $params = Array(
            'popupId'       => $config->getPopupId($order->getStoreId()),
            'jobId'         => $order->getIncrementId(),
            'transactionId' => $order->getIncrementId(),
            'amount'        => $this->calcCentAmount($order->getGrandTotal()),
            'currency'      => $order->getOrderCurrencyCode(),
            'paymentMethod' => $config->getConfigData('paymenttype', $code, $order->getStoreId()),
            'returnUrl'     => Mage::getUrl('expercash/expercash/success', array('_secure' => true)),
            'errorUrl'      => Mage::getUrl('expercash/expercash/error', array('_secure' => true)),
            'notifyUrl'     => Mage::getUrl('expercash/expercash/notify', array('_secure' => true)),
            'profile'       => $config->getProfilId($order->getStoreId()),
        );

        if ($paymentObject instanceof Expercash_Expercash_Model_Expercashpc) {
            $params  = array_merge($params, $this->getBarzahlenParams($order));
        }
        $params['popupKey'] = $this->getPopupKey($order, $params);
        $additionalParams = $this->getNonHashedParams($order);
        $params = array_merge($params, $additionalParams);
        $params = $this->removeEmptyParams($params);

        return $params;
    }

    /**
     * return the additional params that are not included in the hash building process
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function getNonHashedParams(Mage_Sales_Model_Order $order)
    {
        $params = array(
            'cssUrl'          => $this->getConfig()->getCssUrl($order->getStoreId()),
            'language'        => $this->getLocale(),
            'shopEnvironment' => json_encode($this->getShopEnvironmentParams())
        );
        return $params;
    }

    /**
     * remove empty params from param array
     *
     * @param array $params
     *
     * @return array
     */
    protected function removeEmptyParams(array $params)
    {
        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
            }
        }
        return $params;
    }

    /**
     * build and return the popupKey value
     *
     * @param Mage_Sales_Model_Order $order
     * @param array                  $params
     *
     * @return string
     */
    protected function getPopupKey(Mage_Sales_Model_Order $order, array $params)
    {
        $popupKey = implode('', $params) . $this->getConfig()->getAuthorizationkey($order->getStoreId());
        return md5($popupKey);
    }

    /**
     * get the Barzahlen params
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function getBarzahlenParams(Mage_Sales_Model_Order $order)
    {
        /** @var  $billingAddress  Mage_Sales_Model_Order_Address */
        $billingAddress = $order->getBillingAddress();
        return array(
            'customerPrename'  => $billingAddress->getFirstname(),
            'customerName'     => $billingAddress->getLastname(),
            'customerAddress1' => $billingAddress->getStreetFull(),
            'customerZip'      => $billingAddress->getPostcode(),
            'customerCity'     => $billingAddress->getCity(),
            'customerCountry'  => $billingAddress->getCountry(),
            'customerEmail'    => $billingAddress->getEmail(),
            'updateUrl'        => Mage::getUrl('expercash/expercash/notify', array('_secure' => true))
        );
    }

    /**
     * return the params for shopEnvironment
     *
     * @return array
     */
    protected function getShopEnvironmentParams()
    {
        return array(
            'envSystemName'          => Mage::helper('expercash/data')->getEnvName(),
            'envSystemVersion'       => Mage::helper('expercash/data')->getMagentoVersion(),
            'envPaymentModulVersion' => Mage::helper('expercash/data')->getVersion()
        );
    }

    /**
     * get locale
     *
     * @return mixed null|string
     */
    protected function getLocale()
    {
        $result = self::DEFAULT_LOCALE;
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        if (in_array($locale, $this->_supportedLocales)) {
            $result = $locale;
        }
        return $result;
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
     * getter for config model
     *
     * @return Expercash_Expercash_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('expercash/config');
    }
}