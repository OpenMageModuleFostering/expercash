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
    public function testValidateParamsTypeCheck()
    {
        $helper = Mage::helper('expercash/masterpass');
        $helper->validateParams(array(
                'notificationSignature' => true,
            )
        );
    }

    /**
     * @test
     */
    public function testClearMasterpassSessionData()
    {
        $arrayObject = new Varien_Object();
        /** @var Expercash_Expercash_Helper_Masterpass $helper */
        $helper = $this->getHelperMock('expercash/masterpass', array('getCustomerSession'));
        $helper->expects($this->any())
               ->method('getCustomerSession')
               ->will($this->returnValue($arrayObject));
        $arrayObject->setData(Expercash_Expercash_Helper_Masterpass::MASTERPASS_SESSION_KEY, 'foo');
        $helper->clearMasterpassSessionData();
        $this->assertNull($arrayObject->getData(Expercash_Expercash_Helper_Masterpass::MASTERPASS_SESSION_KEY));
    }

    /**
     * @test
     */
    public function testGetArrayHash()
    {
        $helper = Mage::helper('expercash/masterpass');

        $array = array('1' => 'foo', '2' => 'bar');

        $this->assertEquals(md5(json_encode($array)), $helper->getArrayHash($array));

    }

    /**
     * @test
     */
    public function testGetBillingArray()
    {
        $address = new Varien_Object(array(
                'street' => 'Nonnenstraße 11d',
                'totals' => 12345
            )
        );

        $billingArray = Mage::helper('expercash/masterpass')->getBillingArray($address);

        $this->assertEquals($address->getStreet(), $billingArray['street']);
        $this->assertArrayNotHasKey('totals', $billingArray);
        $this->assertNull($billingArray['email']);
    }

    /**
     * @test
     */
    public function testGetOpcTemplate()
    {
        $block = new Varien_Object(array('template' => ''));

        $layoutMock = $this->getModelMock('core/layout', array('getBlock'));
        $layoutMock->expects($this->any())
                   ->method('getBlock')
                   ->with('checkout.onepage')
                   ->will($this->returnValue($block));
        $this->replaceByMock('singleton', 'core/layout', $layoutMock);

        /** @var Expercash_Expercash_Helper_Masterpass $helper */
        $helper = $this->getHelperMock('expercash/masterpass', array('isFullCheckout', 'isCE15'));

        $this->assertEquals('', $helper->getOpcTemplate());

        $helper->expects($this->any())
               ->method('isFullCheckout')
               ->will($this->returnValue(true));

        $this->assertEquals('expercash/checkout/onepage.phtml', $helper->getOpcTemplate());

        $helper->expects($this->once())
               ->method('isCE15')
               ->will($this->returnValue(true));

        $this->assertEquals('expercash/checkout/ce15/onepage.phtml', $helper->getOpcTemplate());
    }

    public function testIsSessionValid()
    {
        /** @var Expercash_Expercash_Helper_Masterpass $helper */
        $helper = Mage::helper('expercash/masterpass');
        $quote = Mage::getModel('sales/quote');
        $address = $this->getModelMock('sales/quote_address',
            array('getShippingRatesCollection', 'getItemsCollection', 'getTotals')
        );
        $address->expects($this->any())
                ->method('getShippingRatesCollection')
                ->will($this->returnValue(new Varien_Object()));
        $address->expects($this->any())
                ->method('getItemsCollection')
                ->will($this->returnValue(new Varien_Object()));
        $address->expects($this->any())
                ->method('getTotals')
                ->will($this->returnValue(array()));


        $address->setStreetFull(array('Street', 'street 2'));
        $quote->setBillingAddress($address);

        $sessionData = array(
            'timestamp'      => time(),
            'billingAddress' => $helper->getArrayHash($helper->getBillingArray($address))
        );

        $this->assertTrue($helper->isSessionValid($sessionData, $quote));

    }
    

    public function testSetBillingDataToQuoteAndSession()
    {
        $params = array(
            "customer_address1"            => "Augustaanlage 59",
            "customer_address2"            => "c/o+Adresszusatzfeld 1",
            "customer_address3"            => "Adresszusatzfeld 2",
            "customer_city"                => "Mannheim",
            "customer_country"             => "DE",
            "customer_country_subdivision" => "Baden-Württemberg",
            "customer_email"               => "masterpass_user@expercash.com",
            "customer_name"                => "Mustermann",
            "customer_prename"             => "Max",
            "customer_telephone"           => "49-6217249380000",
            "customer_zip"                 => "68239",
            "delivery_address1"            => "Neue Strasse 3",
            "delivery_address2"            => "c/o Firma Adresszusatz 1",
            "delivery_address3"            => "Adresszusatz 2",
            "delivery_city"                => "Mannheim",
            "delivery_country"             => "DE",
            "delivery_country_subdivision" => "Baden-Württemberg",
            "delivery_fullname"            => "Max Müstermänner",
            "delivery_telephone"           => "DE+49-6217249380000",
            "delivery_zip"                 => "68165"
        );

        $quote = Mage::getModel('sales/quote');
        $address = $this->getModelMock('sales/quote_address',
            array('getShippingRatesCollection', 'getItemsCollection', 'getTotals')
        );
        $address->expects($this->any())
                ->method('getShippingRatesCollection')
                ->will($this->returnValue(new Varien_Object()));
        $address->expects($this->any())
                ->method('getItemsCollection')
                ->will($this->returnValue(new Varien_Object()));
        $address->expects($this->any())
                ->method('getTotals')
                ->will($this->returnValue(array()));

        $quote->setBillingAddress($address);

        $sessionMock = $this->getModelMock('checkout/session', array('getQuote'));
        $sessionMock->expects($this->any())
                    ->method('getQuote')
                    ->will($this->returnValue($quote));

        $this->replaceByMock('singleton', 'checkout/session', $sessionMock);

        /** @var Expercash_Expercash_Helper_Masterpass $helper */
        $helper = Mage::helper('expercash/masterpass');

        $helper->setBillingDataToQuoteAndSession($params);

        $this->assertEquals($params['customer_address2'], $quote->getBillingAddress()->getCompany());
        $this->assertEquals($params['customer_zip'], $quote->getBillingAddress()->getPostcode());
        $this->assertEquals($params['customer_name'], $quote->getBillingAddress()->getLastname());

    }

}
