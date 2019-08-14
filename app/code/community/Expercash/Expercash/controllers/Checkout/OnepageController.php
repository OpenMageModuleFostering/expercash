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
require_once 'Mage/Checkout/controllers/OnepageController.php';

/**
 * Expercash_Expercash_Checkout_OnepageController
 */
class Expercash_Expercash_Checkout_OnepageController
    extends Mage_Checkout_OnepageController
{
    protected function getAddressKeys()
    {
        return array(
            'email',
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'company',
            'street',
            'city',
            'region',
            'region_id',
            'postcode',
            'country_id',
            'telephone',
            'fax',
            'same_as_billing',
        );
    }

    public function shippingMethodsAction()
    {
        $result = array();

        $result['goto_section'] = 'shipping_method';
        $result['update_section'] = array(
            'name' => 'shipping-method',
            'html' => $this->_getShippingMethodsHtml()
        );

        $result['allow_sections'] = array('shipping');

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function addressesAction()
    {
        $addresses = array();

        $billingAddress = $this->getOnepage()->getQuote()->getBillingAddress();
        $billingAddress->explodeStreetAddress();

        $shippingAddress = $this->getOnepage()->getQuote()->getShippingAddress();
        $shippingAddress->explodeStreetAddress();

        $addressKeys = $this->getAddressKeys();
        foreach ($billingAddress->getStreet() as $i => $line) {
            $addressKeys[] = sprintf('street%d', $i + 1);
        }

        $addresses['billing'] = $billingAddress->toArray($addressKeys);
        $addresses['billing']['use_for_shipping_yes'] = false;
        $addresses['billing']['use_for_shipping_no'] = true;
        $addresses['shipping'] = $shippingAddress->toArray($addressKeys);

        $this->getResponse()->setHeader('Content-type', 'application/x-json');
        $this->getResponse()->setBody(Mage::helper('core/data')->jsonEncode($addresses));
    }

    public function validateAddressesAction()
    {
        $result = array();

        /** @var  $quote Mage_Sales_Model_Quote */
        $quote = $this->getOnePage()->getQuote();
        $methodInstance = $quote->getPayment()->getMethodInstance();

        if ($methodInstance instanceof Expercash_Expercash_Model_Expercashmpf) {
            if (true !== $quote->getBillingAddress()->validate()) {
                $result['error'] = true;
                $result['goto_section'] = 'billing';
            } elseif (true !== $quote->getShippingAddress()->validate()) {
                $result['error'] = true;
                $result['goto_section'] = 'shipping';
            }
        }

        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
