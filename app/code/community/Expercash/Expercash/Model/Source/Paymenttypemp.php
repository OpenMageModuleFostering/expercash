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
class Expercash_Expercash_Model_Source_Paymenttypemp
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => Expercash_Expercash_Model_Expercashmp::PAYMENT_TYPE_MP_BUY,
                'label' => Mage::helper('expercash')->__('MPBUY')
            ),
            array(
                'value' => Expercash_Expercash_Model_Expercashmp::PAYMENT_TYPE_MP_AUTH,
                'label' => Mage::helper('expercash')->__('MPAUTH')
            ),
        );
    }
}