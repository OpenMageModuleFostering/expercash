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
class Expercash_Expercash_Block_Info_Expercash extends Mage_Payment_Block_Info
{
    protected $_expercashStack = false;

    /**
     * Init default template for block
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('expercash/info/expercash.phtml');
    }

    /**
     * Retrieve info model
     *
     * @return Mage_Sofortueberweisung_Model_Info
     */
    public function getInfo()
    {
        $info = $this->getData('info');

        if (!($info instanceof Mage_Payment_Model_Info)) {
            Mage::throwException(
                $this->__('Can not retrieve payment info model object.')
            );
        }
        return $info;
    }

    /**
     * Retrieve payment method model
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethod()
    {
        return $this->getInfo()->getMethodInstance();
    }

    public function toPdf()
    {
        $this->setTemplate('expercash/info/pdf/expercash.phtml');
        return $this->toHtml();
    }

    public function getExperCashInfo($string)
    {
        if ($this->_expercashStack === false):
            $this->_expercashStack = Mage::helper('expercash/payment')
                ->getExperCashData($this->getInfo()->getOrder()->getQuoteId());
        endif;
        $test = isset($this->_expercashStack[0][$string]) ? $this->_expercashStack[0][$string] : false;

        return $test;
    }
}