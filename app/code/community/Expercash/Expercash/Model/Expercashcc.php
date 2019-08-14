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

class Expercash_Expercash_Model_Expercashcc extends Expercash_Expercash_Model_Expercash
{
    const PAYMENT_TYPE_CC_BUY = 'cc_buy';
    const PAYMENT_TYPE_CC_AUTH = 'cc_authorize';
    const PAYMENT_TYPE_CC_CAPTURE = 'cc_capture';
    /**
     * Availability options
     */
    protected $_code = 'expercashcc';
    protected $_paymentMethod = 'expercashcc';
    protected $_formBlockType = 'expercash/form_expercash';
    protected $_infoBlockType = 'expercash/info_expercash';
    protected $_isGateway = false;
    protected $_canAuthorize = true;
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
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        if ($this->getExperCashInfo('expercash_request_type') == self::PAYMENT_TYPE_CC_AUTH) {
            return parent::canCapture();
        }
        return false;
    }

    /**
     * capture the amount with transaction id
     *
     * @access public
     * @param string $payment Varien_Object object
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::getModel('expercash/api_capture')->capture(
            $payment,
            $amount,
            self::PAYMENT_TYPE_CC_CAPTURE
        );
    }

    /**
     * @return bool
     */
    public function isDirectSaleEnabled()
    {
        return $this->getConfigData('paymenttype') == self::PAYMENT_TYPE_CC_BUY;
    }

    
}