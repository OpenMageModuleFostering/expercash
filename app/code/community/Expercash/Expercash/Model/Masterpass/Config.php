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

class Expercash_Expercash_Model_Masterpass_Config extends  Expercash_Expercash_Model_Config
{

    /**
     * Start fullcheckout action
     *
     * @var string
     */
    protected $_startAction = 'expercash/fullcheckout/start';

    /**
     * store id
     *
     * @var null
     */
    protected $storeId      = null;


    /**
     * set store id for config object in constructor
     */
    public function __construct()
    {
        $this->storeId = Mage::app()->getStore()->getId();
    }


    /**
     * get route for masterpass full checkout
     *
     * @return string
     */
    protected function getMasterPassFullCheckoutStartAction()
    {
        return $this->_startAction;
    }

    /**
     * show master Full Checkout Button in Mini Cart
     *
     * @return bool
     */
    public function showButtonInMiniCart()
    {
        return Mage::getStoreConfigFlag('payment/expercashmpf/show_button_in_mini_cart', $this->storeId);
    }


    /**
     * show masterpass full checkout button in cart
     *
     * @return bool
     */
    public function showButtonInCart()
    {
        return Mage::getStoreConfigFlag('payment/expercashmpf/show_button_in_cart', $this->storeId);
    }

    /**
     * show masterpass learn more link (checkout method)
     *
     * @return bool
     */
    public function showMasterPassLearnMoreLink()
    {
        return Mage::getStoreConfigFlag('payment/expercashmp/show_learn_more_link', $this->storeId);
    }

    /**
     * show masterpass learn more link ( full checkout method)
     *
     * @return bool
     */
    public function showMasterPassFullCheckoutLearnMoreLink()
    {
        return Mage::getStoreConfigFlag('payment/expercashmpf/show_learn_more_link', $this->storeId);
    }

    /**
     * get image url for masterpass full checkout image
     *
     * @return string
     */
    public function getFullCheckoutImageUrl()
    {
        return Mage::getDesign()->getSkinUrl('images/expercash/mp_buy_with_button.png');
    }

    /**
     * get masterpass full checkout url (route)
     *
     * @return string
     */
    public function getMasterpassFullCheckoutUrl()
    {
        return Mage::getUrl($this->getMasterPassFullCheckoutStartAction());
    }


    /**
     * show masterpass logo in checkout
     *
     * @return bool
     */
    public function showMasterpassLogoInCheckout()
    {
        return Mage::getStoreConfigFlag('payment/expercashmp/show_logo', $this->storeId);
    }
    /**
     * get masterpass learn more basis url from config
     *
     * @return string
     */
    public function getMasterLearnMoreBaseUrl()
    {
        return Mage::getStoreConfig('payment/expercashmp/mp_learnmore_url');
    }


    /**
     * get logo for masterpass checkout payment method
     *
     * @return string
     */
    public function getMasterPassCheckoutLogo()
    {
        return Mage::getDesign()->getSkinUrl('images/expercash/mp_ident.png');
    }

    /**
     * Get Masterpass Learn More Url based on locale.
     * Will use Magento default locale (en_US) if parameter is null
     *
     * @param Mage_Core_Model_Locale $locale
     *
     * @return string
     */
    public function getMasterpassLearnMoreUrl(Mage_Core_Model_Locale $locale = null)
    {
        if (null === $locale) {
            $locale = Mage::getModel('core/locale')->getLocale();
        }

        $shouldEmulate = (null !== $this->storeId) && (Mage::app()->getStore()->getId() != $this->storeId);
        if ($shouldEmulate) {
            $locale->emulate($this->storeId);
        }
        $language = $locale->getLocale()->getLanguage();
        $countryCode = $locale->getLocale()->getRegion();
        if ($shouldEmulate) {
            $locale->revert();
        }

        return $this->getMasterLearnMoreBaseUrl() . DS . strtolower($language) . DS . $countryCode;
    }

    /**
     * get masterpass fullcheckout text
     *
     * @return string
     */
    public function getCheckoutText()
    {
       return Mage::getStoreConfig('payment/expercashmpf/checkout_text');
    }

    /**
     * getter for show checkout text
     *
     * @return bool
     */
    public function isShowCheckoutTextEnabled()
    {
        return Mage::getStoreConfigFlag('payment/expercashmpf/show_checkout_text');
    }

    public function isShowCCDataInCheckoutEnabled()
    {
        return Mage::getStoreConfigFlag('payment/expercashmpf/show_creditcard_data_in_checkout');
    }

}
