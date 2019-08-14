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

/**
 * PayPal Standard payment "form"
 */
class Expercash_Expercash_Block_Masterpass_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_methodCode = "expercashmp";


    /**
     * Set template and add logo if needed
     */
    protected function _construct()
    {
        $this->setTemplate('expercash/form/expercashiframe.phtml');
        $logo = $this->handleLogoDisplay();
        if ($logo !== null) {
            $this->setTemplate('expercash/form/expercashiframe.phtml')
                ->setMethodTitle('')
                ->setMethodLabelAfterHtml($logo->toHtml());
        }

        return parent::_construct();
    }

    /**
     * Payment method code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }



    /**
     * handles the display of Masterpass Logo based on config setting
     *
     * @return null|object
     */
    protected function handleLogoDisplay()
    {
        $config   = $this->getMasterpassConfig();
        $mark = null;
        if ($config->showMasterpassLogoInCheckout() == true) {
            $mark = Mage::app()->getLayout()->createBlock('core/template');
            $mark->setTemplate('expercash/payment/logo/logo.phtml')
                ->setPaymentAcceptanceMarkHref($config->getMasterpassLearnMoreUrl(Mage::app()->getLocale()))
                ->setPaymentAcceptanceMarkSrc($config->getMasterPassCheckoutLogo());
        }

        return $mark;
    }

    /**
     * get masterpass config model
     *
     * @return Expercash_Expercash_Model_Masterpass_Config
     */
    protected function getMasterpassConfig()
    {
        return Mage::getModel('expercash/masterpass_config');
    }



}
