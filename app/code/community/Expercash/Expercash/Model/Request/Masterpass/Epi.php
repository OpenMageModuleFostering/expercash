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

class Expercash_Expercash_Model_Request_Masterpass_Epi
{
    const MASTERPASS_ACTION = 'masterpass';

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
    public function getEpiParams(Mage_Sales_Model_Quote $quote)
    {
        $config = $this->getConfig();
        $storeId = $quote->getStoreId();

        $params = Array(
            'pid'             => $config->getProjectId($storeId),
            'pkey'            => $config->getGatewayKey($storeId),
            'action'          => self::MASTERPASS_ACTION,
            'amount'          => $this->calcCentAmount($quote->getGrandTotal()),
            'currency'        => $quote->getQuoteCurrencyCode(),
            'cref'            => $quote->getReservedOrderId(),
            'bc_ref'          => $quote->getReservedOrderId(),
            'return_url'      => Mage::getUrl('expercash/fullcheckout/success', array('_secure' => true)),
            'notify_url'      => Mage::getUrl('expercash/fullcheckout/notify', array('_secure' => true)),
            'error_url'       => Mage::getUrl('expercash/fullcheckout/error', array('_secure' => true)),
            'shopEnvironment' => $this->getConfig()->getShopEnvironmentParams(),
        );

        return $params;
    }

    public function getPlaceOrderParams(Mage_Sales_Model_Order $order, $captureType)
    {
        $config  = $this->getConfig();
        $storeId = $order->getStoreId();

        $params = Array(
            'pid'             => $config->getProjectId($storeId),
            'pkey'            => $config->getGatewayKey($storeId),
            'action'          => $captureType,
            'reference'       => Mage::helper('expercash/payment')->getPaymentReferenceId($order->getPayment()),
            'amount'          => $this->calcCentAmount($order->getGrandTotal()),
            'currency'        => $order->getOrderCurrencyCode(),
            'cref'            => $order->getIncrementId(),
            'bc_ref'          => $order->getIncrementId(),
            'cip'             => $order->getRemoteIp() ? $order->getRemoteIp() : '10.10.10.10',
            'cid'             => $order->getCustomerId() ? $order->getCustomerId() : 'Guest',
            'shopEnvironment' => $this->getConfig()->getShopEnvironmentParams(),
        );

        return $params;

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
     * getter for masterpass config model
     *
     * @return Expercash_Expercash_Model_Masterpass_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('expercash/masterpass_config');
    }
}