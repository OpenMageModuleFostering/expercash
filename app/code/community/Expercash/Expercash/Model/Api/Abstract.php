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
abstract class Expercash_Expercash_Model_Api_Abstract
{

    /**
     * Max. amount of redirections to follow
     */
    const MAXREDIRECTS = 2;

    /**
     * Timeout in seconds before closing the connection
     */
    const TIMEOUT = 30;

    /**
     * Transport layer for SSL
     */
    const SSLTRANSPORT = 'tcp';


    /**
     * @var string the client uri
     */
    protected $urlToCall = null;

    /**
     * @var Expercash_Expercash_Model_Config
     */
    protected $config = null;

    /**
     * set the client uri
     *
     * @param string $url
     */
    public function setUrlToCall($url)
    {
        $this->urlToCall = $url;
    }

    /**
     * setter for config object
     *
     * @param Expercash_Expercash_Model_Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }


    /**
     * get a Varien_Http_Client
     *
     * @return Varien_Http_Client
     */
    public function getClient()
    {
        return new Varien_Http_Client();
    }

    /**
     * get the config object
     *
     * @return Expercash_Expercash_Model_Config|
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->config = Mage::getModel('expercash/config');
        }
        return $this->config;
    }

    /**
     * return the client uri to call
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getUrlToCall($storeId)
    {
        if (null === $this->urlToCall) {
            $this->urlToCall = $this->getConfig()->getEpiUrl($storeId);
        }
        return $this->urlToCall;
    }

    /**
     * send request via POST to epi gateway
     *
     * @param array $request
     * @param       int storeId
     *
     * @return xml
     * @throws Mage_Core_Exception - on errors
     */
    protected function _postRequest(array $request, $storeId = null)
    {
        $client = $this->getClient();
        $client->setUri($this->getUrlToCall($storeId));
        $client->setConfig(
            array(
                'maxredirects' => self::MAXREDIRECTS,
                'timeout'      => self::TIMEOUT,
                'ssltransport' => self::SSLTRANSPORT,
            )
        );

        $client->setParameterPost($request);
        $client->setMethod(Zend_Http_Client::POST);
        $this->getDataHelper()->clientLog(Expercash_Expercash_Helper_Data::LOG_TYPE_REQUEST, $request);

        try {
            $response = $client->request();
            $responseBody = $response->getBody();
            $this->getDataHelper()->clientLog(Expercash_Expercash_Helper_Data::LOG_TYPE_RESPONSE, $responseBody);
            return $responseBody;

        } catch (Exception $e) {
            Mage::throwException(
                $this->getDataHelper()->__('Gateway request error: %s', $e->getMessage())
            );
        }
    }

    /**
     * Parse the bodytext into simpleXML object and returns it
     *
     * @param xml $bodytext
     *
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function parseResponse($bodyText)
    {
        try {
            libxml_use_internal_errors(true);
            $simpleXMLResponse = simplexml_load_string($bodyText);

            if (false === $simpleXMLResponse
                || true === is_null($simpleXMLResponse)
                || !$simpleXMLResponse instanceof SimpleXMLElement
            ) {
                Mage::throwException(
                    $this->getDataHelper()->__('Error while transforming response to simple xml.')
                );
            }

            return $simpleXMLResponse;

        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * get expercash data helper
     *
     * @return Expercash_Expercash_Helper_Data
     */
    protected function getDataHelper()
    {
        $dataHelper = Mage::helper('expercash/data');
        /**@var $dataHelper Expercash_Expercash_Helper_Data * */
        return $dataHelper;
    }
}
