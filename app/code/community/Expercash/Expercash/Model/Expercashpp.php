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

class Expercash_Expercash_Model_Expercashpp extends Expercash_Expercash_Model_Expercash
{
    const PAYMENT_TYPE_PP = 'PayPal';
    /**
     * Availability options
     */
    protected $_code = 'expercashpp';
    protected $_paymentMethod = 'expercashpp';
    protected $_formBlockType = 'expercash/form_expercash';
    protected $_infoBlockType = 'expercash/info_expercash';
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
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
        return explode(
            ',', $this->getConfigData(
                'allowed_currency', $storeId
            )
        );
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return false;
    }

    
}