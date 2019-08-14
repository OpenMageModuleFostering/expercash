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
class Expercash_Expercash_Model_Api_Masterpass_Epi extends Expercash_Expercash_Model_Api_Abstract
{
    /**
     * code for successful transaction
     */
    const TRANSACTION_SUCCESSFUL = 100;

    const TRANSACTION_ERROR = 900;


    public function doEpiRequest(array $requestParams)
    {
        $this->setUrlToCall($this->getConfig()->getEpiUrl());
        $response = $this->_postRequest($requestParams, null);
        $responseArray = $this->parseResponse($response);
        $this->validateResponse($responseArray);
        return $responseArray;

    }

    /**
     * validate the response and return the token
     *
     * @param  string   $response
     *
     * @throws Mage_Core_Exception - in case of invalid response or errors
     * @return mixed string
     */
    public function validateResponse($responseArray)
    {
        if (!array_key_exists('rc',$responseArray) ||
            $responseArray['rc'] != self::TRANSACTION_SUCCESSFUL
            )
        {
            Mage::throwException('Invalid response from expercash.');
        }
    }

    /**
     * get core data helper
     *
     * @return Mage_Core_Helper_Abstract|Mage_Core_Helper_Data
     */
    protected function getCoreDataHelper()
    {
        $coreDataHelper = Mage::helper('core/data');
        /**@var $coreDataHelper Mage_Core_Helper_Data **/
        return $coreDataHelper;
    }

    /**
     * @param $response
     *
     * @return array
     */
    public function parseResponse($response)
    {
        $simpleXmlResponse = parent::parseResponse($response);
        $responseArray = $this->getCoreDataHelper()->xmlToAssoc($simpleXmlResponse);
        return $responseArray;
    }
}
    