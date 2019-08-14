<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Expercash
 * @package    Expercash_Expercash
 * @copyright  Copyright (c) 2008-2011 bjoern hahnefeld IT
 * @copyright  Copyright (c) 2013 Netresearch App Factory AG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Expercash_Expercash_Test_Block_Masterpass_Fullcheckout_ShortcutTest extends EcomDev_PHPUnit_Test_Case
{



    public function testIsCustomerNotLoggedIn()
    {
        $customerSessionMock = $this->getModelMock('customer/session', array('init', 'start', 'isLoggedIn'));
        $customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'customer/session', $customerSessionMock);

        $block = new Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut();

        $this->assertFalse(
            $this->invokeMethod('isCustomerLoggedIn', array()),
            Mage::getModel('customer/session')->isLoggedIn()
        );
    }


    public function testIsCustomerLoggedIn()
    {
        $customerSessionMock = $this->getModelMock('customer/session', array('init', 'start', 'isLoggedIn'));
        $customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'customer/session', $customerSessionMock);

        $this->assertTrue(
            $this->invokeMethod('isCustomerLoggedIn', array()),
            Mage::getModel('customer/session')->isLoggedIn()
        );
    }


    public function testGetMasterpassConfig()
    {
        $configModel = $this->invokeMethod('getMasterpassConfig');
        $this->assertTrue($configModel instanceof Expercash_Expercash_Model_Masterpass_Config);
    }


    public function testGetLearnMoreLink()
    {
        $configModelMock = $this->getModelMock('expercash/masterpass_config', array(
                'showMasterPassFullCheckoutLearnMoreLink',
                'getMasterpassLearnMoreUrl'
            )
        );
        $configModelMock->expects($this->any())
            ->method('showMasterPassFullCheckoutLearnMoreLink')
            ->will($this->returnValue(true));

        $configModelMock->expects($this->any())
            ->method('getMasterpassLearnMoreUrl')
            ->will($this->returnValue('www.masterpass.com/learnmore/De/de'));
        $this->replaceByMock('model', 'expercash/masterpass_config', $configModelMock);

        $block = new Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut();
        $this->assertEquals('www.masterpass.com/learnmore/De/de', $block->getLearnMoreLink());

    }

    public function testGetLearnMoreLinkDisabled()
    {
        $configModelMock = $this->getModelMock('expercash/masterpass_config', array(
                'showMasterPassFullCheckoutLearnMoreLink',
            )
        );
        $configModelMock->expects($this->any())
            ->method('showMasterPassFullCheckoutLearnMoreLink')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'expercash/masterpass_config', $configModelMock);

        $block = new Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut();
        $this->assertNull($block->getLearnMoreLink());

    }


    public function testGetImageUrl()
    {
        $configModelMock = $this->getModelMock('expercash/masterpass_config', array('getFullCheckoutImageUrl'));
        $configModelMock->expects($this->any())
            ->method('getFullCheckoutImageUrl')
            ->will($this->returnValue('www.myImage.de'));
        $this->replaceByMock('model', 'expercash/masterpass_config', $configModelMock);

        $block = new Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut();

        $this->assertEquals('www.myImage.de', $block->getImageUrl());
        $this->assertNotEquals('www.notMyImage.de', $block->getImageUrl());

    }


    public function testGetCheckoutUrl()
    {
        $configModelMock = $this->getModelMock('expercash/masterpass_config', array('getMasterpassFullCheckoutUrl'));
        $configModelMock->expects($this->any())
            ->method('getMasterpassFullCheckoutUrl')
            ->will($this->returnValue('www.myImage.de/fullcheckout/start'));
        $this->replaceByMock('model', 'expercash/masterpass_config', $configModelMock);

        $block = new Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut();

        $this->assertEquals('www.myImage.de/fullcheckout/start', $block->getCheckoutUrl());
        $this->assertNotEquals('www.myImage.de/fullcheckout/foo', $block->getCheckoutUrl());

    }


    public function testToHtmlShouldRender()
    {
        $this->assertEquals('', $this->invokeMethod('_toHtml'));

    }

    public function testToHtmlShouldNotRender()
    {

        $sessionMock = $this->getModelMock('customer/session', array('init', 'start'));
        $this->replaceByMock('model', 'customer/session', $sessionMock);

        $sessionMock = $this->getModelMock('checkout/session', array('init', 'start'));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);

        $blockMock = $this->getBlockMock('expercash/masterpass_fullcheckout_shortcut', array(
                '_beforeToHtml',

            )
        );

        $blockMock->setShouldRender(false);
        $this->assertEquals('', $blockMock->toHtml());

    }


    /**
     * Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod($methodName, array $parameters = array())
    {
        $object = new Expercash_Expercash_Block_Masterpass_Fullcheckout_Shortcut();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }


}
