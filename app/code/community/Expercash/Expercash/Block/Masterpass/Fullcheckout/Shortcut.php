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
class Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut extends Mage_Core_Block_Template
{
    /**
     * layout name for cart top button
     */
    const LAYOUTNAME_TOP     = 'checkout.cart.methods.masterpass_fullcheckout.top';

    /**
     * layout name for cart bottom button
     */
    const LAYOUTNAME_BOTTOM  = 'checkout.cart.methods.masterpass_fullcheckout.bottom';

    /**
     * layout name for mini cart button
     */
    const LAYOUTNAME_SIDEBAR = 'expercash.cart_sidebar.shortcut';

    const LAYOUTNAME_MINI_CART ="expercash.masterpass.mini_cart.shortcut";

    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode = 'expercashmpf';


    /**
     * setter for shouldRender property
     *
     * @param $var
     */
    public function setShouldRender($var)
    {
        $this->_shouldRender = $var;
    }

    /**
     * getter for shouldRender property
     *
     * @return bool
     */
    public function getShouldRender()
    {
        return $this->_shouldRender;
    }

    /**
     * determine if masterpass button should be displayed and set shouldrender accordingly
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $result       = parent::_beforeToHtml();
        $nameInLayout = $this->getNameInLayout();
        $quote        = Mage::getSingleton('checkout/session')->getQuote();
        $config       = $this->getMasterpassConfig();

        // check if full checkout is disabled
        if (!$config->getConfigData('active', $this->_paymentMethodCode)) {
            $this->setShouldRender(false);
            return $result;
        }

        //check if customer is logged in, do not render block if customer is logged in
        if ($this->isCustomerLoggedIn()) {
            $this->setShouldRender(false);
            return $result;
        }

        if ($this->isMiniCart() && !$config->showButtonInMiniCart()) {
            $this->setShouldRender(false);
            return $result;
        }

        // check visibility on cart and mini cart
        if (($this->isCartBottom()|| $this->isCartTop())
            && !$config->showButtonInCart()
        ) {
            $this->setShouldRender(false);
            return $result;
        }

        if ($nameInLayout === self::LAYOUTNAME_SIDEBAR && !$config->showButtonInMiniCart()) {
            $this->setShouldRender(false);
            return $result;

        }



        // validate minimum quote amount and validate quote for zero grandtotal
        if (null !== $quote && (!$quote->validateMinimumAmount()
            || (!$quote->getGrandTotal()))
        ) {
            $this->setShouldRender(false);
            return $result;
        }


        if (false === Mage::helper('checkout')->isAllowedGuestCheckout($quote)) {
            $this->setShouldRender(false);
            return $result;
        }

        // check payment method availability
        $methodInstance = Mage::helper('payment')->getMethodInstance($this->_paymentMethodCode);
        if (!$methodInstance) {
            $this->setShouldRender(false);
            return $result;
        }

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getShouldRender()) {
            return '';
        }
        return parent::_toHtml();
    }


    /**
     * getter for masterpass config model
     *
     * @return Expercash_Expercash_Model_Masterpass_Config
     */
    public function getMasterpassConfig()
    {
        return Mage::getModel('expercash/masterpass_config');
    }

    /**
     * get image url for masterpass full checkout
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->getMasterpassConfig()->getFullCheckoutImageUrl();
    }

    /**
     * getter for masterpass full checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getMasterpassConfig()->getMasterpassFullCheckoutUrl();
    }

    /**
     * returns show more link if enabled for masterpass full checkout
     *
     * @return null|string
     */
    public function getLearnMoreLink()
    {
        $result = null;
        $locale = Mage::app()->getLocale();
        if ($this->getMasterpassConfig()->showMasterPassFullCheckoutLearnMoreLink()) {
            $result = $this->getMasterpassConfig()->getMasterpassLearnMoreUrl($locale);
        }

        return $result;
    }

    /**
     * check if actual layout name is cart top
     *
     * @return bool
     */
    public function isCartTop()
    {
        if ($this->getNameInLayout() === self::LAYOUTNAME_TOP) {
            return true;
        }
    }

    /**
     * check if actual layout name is mini cart
     *
     * @return bool
     */
    public function isMiniCart()
    {
        if ($this->getNameInLayout() === self::LAYOUTNAME_MINI_CART) {
            return true;
        }

    }

    /**
     * check if actual layout name is cart bottom
     *
     * @return bool
     */
    public function isCartBottom()
    {
        if ($this->getNameInLayout() === self::LAYOUTNAME_BOTTOM) {
            return true;
        }

    }


    /**
     * check if customer is logged in
     *
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        $result = false;
        $customer = Mage::getModel('customer/session');
        if ($customer->isLoggedIn()) {
            $result = true;
        }

        return $result;
    }

}
