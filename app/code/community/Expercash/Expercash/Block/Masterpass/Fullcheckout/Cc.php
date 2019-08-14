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
class Expercash_Expercash_Block_Masterpass_Fullcheckout_Cc extends Expercash_Expercash_Block_Masterpass_Form
{
    protected function _construct()
    {
        parent::_construct();

        if ($this->getMasterpassConfig()->isShowCCDataInCheckoutEnabled()) {
            $this->setTemplate('expercash/payment/masterpass/cc.phtml');
        }
    }

    /**
     * get quote from session
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getModel('checkout/session')->getQuote();
    }

    /**
     * get masked cc number from payment
     *
     * @return string
     */
    public function getMaskedPan()
    {
        return $this->getQuote()->getPayment()->getAdditionalInformation('maskedPan');
    }

    /**
     * get card name from payment
     *
     * @return string
     */
    public function getCardSchema()
    {
        return $this->getQuote()->getPayment()->getAdditionalInformation('cardScheme');
    }
}
