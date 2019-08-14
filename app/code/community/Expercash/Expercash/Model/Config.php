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

class Expercash_Expercash_Model_Config
{

    /*
     * the payment service path
     */
    const PAYMENT_SERVICE_PATH = 'payment_services/expercash/';


    /*
    *  expercash token config path
    */
    const TOKEN_REGISTRY_KEY = 'expercash_token';

    /**
     * get the epi url from the config.xml
     *
     * @return string
     */
    public function getEpiUrl($storeId = null)
    {
        return Mage::getStoreConfig('epi_url/url', $storeId);
    }

    /**
     * generic getter for config values
     *
     * @param string $field
     * @param string $code
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $code, $storeId = null)
    {
        $path = 'payment/' . $code . '/' . $field;
        $config = Mage::getStoreConfig($path, $storeId);
        return $config;
    }

    /**
     * returns the additional payment params that are configured in config.xml
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAdditionalPaymentParams($storeId = null)
    {
        return Mage::getStoreConfig('additionalPaymentParams/params', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getPopupId($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'popup_id', $storeId);
    }

    /**
     * returns the profil id
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getProfilId($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'profil_id', $storeId);
    }

    /**
     * returns the expercash authorization key
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getAuthorizationkey($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'authorization_key', $storeId);
    }

    /**
     * returns the expercash gateway key
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getGatewayKey($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'gateway_key', $storeId);
    }

    /**
     * returns the project id
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getProjectId($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'project_id', $storeId);
    }

    /**
     * returns the iframe css class
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getIframeCssClass($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'iframe_css_class', $storeId);
    }

    /**
     * returns the iframe width
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getIframeWidth($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'iframe_width', $storeId);
    }

    /**
     * returns the iframe height
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getIframeHeight($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'iframe_height', $storeId);
    }

    /**
     * returns the css url
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getCssUrl($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICE_PATH . 'css_url', $storeId);
    }

    /**
     * get the iframe token url from config
     * there is no need to support multistore for this, as the url should be the same for all stores
     *
     * @return string
     */
    public function getIframeTokenUrl()
    {
        return Mage::getStoreConfig('iframe_token_url/url');
    }

    /**
     * get the iframe url from config
     * there is no need to support multistore for this, as the url should be the same for all stores
     *
     * @return string
     */
    public function getIframeUrl()
    {
        return Mage::getStoreConfig('iframe_url/url');
    }

    /**
     * return the params for shopEnvironment
     *
     * @return array
     */
    public function getShopEnvironmentParams()
    {
        $params = array(
            'envSystemName'          => Mage::helper('expercash/data')->getEnvName(),
            'envSystemVersion'       => Mage::helper('expercash/data')->getMagentoVersion(),
            'envPaymentModulVersion' => Mage::helper('expercash/data')->getVersion()
        );

        return json_encode($params);
    }


}
