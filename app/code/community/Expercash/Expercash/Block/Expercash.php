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
class Expercash_Expercash_Block_Expercash extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('expercash/expercash.phtml');
    }

    /**
     * get iframe css class from config
     *
     * @return string
     */
    protected function getIframeCssClass()
    {
        return $this->getConfig()->getIframeCssClass($this->getStoreId());
    }

    /**
     * get iframe width from config
     *
     * @return string
     */
    protected function getIframeWidth()
    {
        return $this->getConfig()->getIframeWidth($this->getStoreId());
    }

    /**
     * get iframe height from config
     *
     * @return string
     */
    protected function getIframeHeight()
    {
        return $this->getConfig()->getIframeHeight($this->getStoreId());
    }

    /**
     * @return string
     */
    protected function getIframeUrl()
    {
        $iframeModel   = $this->getIframeModel();
        $iframeModel->setOrderDataToSession();
        $iframeUrl = $iframeModel->getIframeUrl();
        Mage::helper('expercash/data')->log(sprintf("Create iframe-url:\n%s", $iframeUrl));

        return $iframeUrl;
    }

    /**
     * getter for iframe model
     *
     * @return Expercash_Expercash_Model_Request_Iframe
     */
    protected function getIframeModel()
    {
        return Mage::getModel('expercash/request_iframe');
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

    protected function getStoreId()
    {
        return $this->getIframeModel()->getOrder()->getStoreId();
    }

}