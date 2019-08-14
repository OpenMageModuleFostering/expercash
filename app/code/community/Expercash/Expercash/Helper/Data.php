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

class Expercash_Expercash_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * log request type string
     */
    const LOG_TYPE_REQUEST = "REQUEST";

    /**
     * log response type string
     */
    const LOG_TYPE_RESPONSE = "RESPONSE";

    const ENV_NAME = "Magento";

    /**
     * log to a separate log file
     *
     * @param string $message
     * @param int    $level
     * @param bool   $force
     * @return Expercash_Expercash_Helper_Data
     */
    public function log($message, $level = null)
    {
        //Reformat message for better log-visibility
        $message = sprintf("\n=====================\n%s\n=====================", $message);
        Mage::log($message, $level, 'expercash.log', true);
        return $this;
    }
    
    /**
     * refill cart
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    public function refillCart($order)
    {
        //Get cart singleton
        $cart = Mage::getSingleton('checkout/cart');

        if (0 < $cart->getQuote()->getItemsCollection()->count()) {
            //cart is not empty, so refilling it is not required
            return;
        }
        foreach ($order->getItemsCollection() as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        }
        $cart->save();

        // add coupon code
        $coupon = $order->getCouponCode();
        $session = Mage::getSingleton('checkout/session');
        if (false === is_null($coupon)) {
            $session->getQuote()->setCouponCode($coupon)->save();
        }
    }
    
    /**
     * returns the modul version from config.xml
     * 
     * @return string
     */
    public function getVersion()
    {
        return (string) Mage::getConfig()->getNode('modules')
                                         ->children()
                                         ->Expercash_Expercash->version;
    }
    
    /**
     * returns the Magento versions number
     * 
     * @return string
     */
    public function getMagentoVersion()
    {
        return (string) Mage::getVersion();
    }
    
    /**
     * returns the shopsystem Name
     * 
     * @return string
     */
    public function getEnvName()
    {
        return self::ENV_NAME;
    }
    
    /**
     * get the additional payment params from the config.xml
     * 
     * @return array
     */
    public function getAdditionalPaymentParams()
    {
        return explode(",", Mage::getModel('expercash/config')->getAdditionalPaymentParams());
    }

    /**
     * log client response and request
     *
     * @param string $logType
     * @param mixed  $logValues
     */
    public function clientLog($logType, $logValues)
    {
        $message = "";
        if ($logType === self::LOG_TYPE_REQUEST) {
            $message = "Request to Expercash with following params:\n%s";
        }

        if ($logType === self::LOG_TYPE_RESPONSE) {
            $message = "Gateway gave following response:\n%s";;
        }
        $this->log(sprintf($message, Zend_Json::encode($logValues)));
    }

    /**
     * Add request parameter into url
     *
     * @param  $url string
     * @param  $param array( 'key' => value )
     * @return string
     */
    public function addRequestParam($url, $param)
    {
        $startDelimiter = (false === strpos($url,'?'))? '?' : '&';

        $arrQueryParams = array();
        foreach ($param as $key => $value) {
            if (is_numeric($key) || is_object($value)) {
                continue;
            }

            if (is_array($value)) {
                // $key[]=$value1&$key[]=$value2 ...
                $arrQueryParams[] = $key . '[]=' . implode('&' . $key . '[]=', $value);
            } elseif (is_null($value)) {
                $arrQueryParams[] = $key;
            } else {
                $arrQueryParams[] = $key . '=' . $value;
            }
        }
        $url .= $startDelimiter . implode('&', $arrQueryParams);

        return $url;
    }

    public function isBelowCE17()
    {
        $result = false;
        if (Mage::getVersion() < '1.7.0') {
            $result = true;
        }

        return $result;
    }
}