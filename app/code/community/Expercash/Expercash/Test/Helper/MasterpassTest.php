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
 * Expercash_Expercash_Test_Helper_MasterpassTest
 *
 * @category    Expercash
 * @package     Expercash_Expercash
 */
class Expercash_Expercash_Test_Helper_MasterpassTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Validate params: Signature and build hash do not match!
     */
    public function validateParamsTypeCheck()
    {
        $helper = Mage::helper('expercash/masterpass');
        $helper->validateParams(array(
            'notificationSignature' => true,
        ));
    }
}
