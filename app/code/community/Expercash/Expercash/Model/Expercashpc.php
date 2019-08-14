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

class Expercash_Expercash_Model_Expercashpc extends Expercash_Expercash_Model_Expercash
{

    /**
     * payment type name
     */
    const PAYMENT_TYPE_PC = 'barzahlen';

    const PAYMENT_METHOD_NAME = 'expercashpc';

    const ALLOWED_MAX_AMOUNT = 1000;

    const BARZAHLEN_STATUS_PAID = 'PAID';

    const BARZAHLEN_STATUS_OPEN = 'OPEN';


    /**
     * Availability options
     */
    protected $_code = 'expercashpc';
    protected $_canCapture = false;
    protected $_canCapturePartial = false;


    /**
     * overwrite isAvailable and add checks for currency code, country code and allowed max amount
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $result    = false;
        $available = parent::isAvailable($quote);

        if ($available
            && $quote instanceof Mage_Sales_Model_Quote
            && $quote->getQuoteCurrencyCode() === parent::getConfigData('allowed_currency')
            && $quote->getBillingAddress()->getCountryId() === parent::getConfigData('specificcountry')
            && $quote->getGrandTotal() < self::ALLOWED_MAX_AMOUNT
        ) {
            $result = true;
        }
        return $result;
    }

    /**
     * return the paymenttype or the other config values based on field value
     *
     * @param string $field
     * @param int    $storeId
     *
     * @return string
     */
    public function getConfigData($field, $storeId = null)
    {
        if ($field == 'paymenttype') {
            return self::PAYMENT_TYPE_PC;
        }
        return parent::getConfigData($field, $storeId);
    }

}