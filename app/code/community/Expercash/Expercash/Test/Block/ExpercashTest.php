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
class Expercash_Expercash_Test_Block_ExpercashTest extends EcomDev_PHPUnit_Test_Case
{


    public function testGetIframeHeight()
    {
        $checkoutSessionMock = $this->getModelMock('checkout/session', array('init', 'start'));
        $this->replaceByMock('model', 'checkout/session', $checkoutSessionMock);

        $configMock = $this->getModelMock('expercash/config', array('getIframeHeight'));
        $configMock->expects($this->any())
            ->method('getIframeHeight')
            ->will($this->returnValue(150));
        $this->replaceByMock('model', 'expercash/config', $configMock);

        $blockMock = $this->getBlockMock('expercash/expercash', array('getStoreId', 'getConfig'));
        $blockMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->replaceByMock('block', 'expercash/expercash', $blockMock);

        $this->assertEquals(150, $this->invokeMethod('getIframeHeight', array()));
    }

    public function testGetIframeWidth()
    {
        $checkoutSessionMock = $this->getModelMock('checkout/session', array('init', 'start'));
        $this->replaceByMock('model', 'checkout/session', $checkoutSessionMock);

        $configMock = $this->getModelMock('expercash/config', array('getIframeWidth'));
        $configMock->expects($this->any())
            ->method('getIframeWidth')
            ->will($this->returnValue(250));
        $this->replaceByMock('model', 'expercash/config', $configMock);

        $blockMock = $this->getBlockMock('expercash/expercash', array('getStoreId', 'getConfig'));
        $blockMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->replaceByMock('block', 'expercash/expercash', $blockMock);

        $this->assertEquals(250, $this->invokeMethod('getIframeWidth', array()));
    }

    public function testGetIframeCssClass()
    {
        $checkoutSessionMock = $this->getModelMock('checkout/session', array('init', 'start'));
        $this->replaceByMock('model', 'checkout/session', $checkoutSessionMock);

        $configMock = $this->getModelMock('expercash/config', array('getIframeCssClass'));
        $configMock->expects($this->any())
            ->method('getIframeCssClass')
            ->will($this->returnValue('cssClassFoobar'));
        $this->replaceByMock('model', 'expercash/config', $configMock);

        $blockMock = $this->getBlockMock('expercash/expercash', array('getStoreId', 'getConfig'));
        $blockMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->replaceByMock('block', 'expercash/expercash', $blockMock);

        $this->assertEquals('cssClassFoobar', $this->invokeMethod('getIframeCssClass', array()));
    }


    public function testGetStoreId()
    {
        $order = new Varien_Object();
        $order->setStoreId(1);

        $iframeMock = $this->getModelMock('expercash/request_iframe', array('getOrder'));
        $iframeMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'expercash/request_iframe', $iframeMock);

        $this->assertEquals(1, $this->invokeMethod('getStoreId', array()));
    }

    public function testGetIframeModel()
    {
        $this->assertTrue(
            $this->invokeMethod('getIframeModel', array()) instanceof Expercash_Expercash_Model_Request_Iframe
        );
    }

    public function testGetConfig()
    {
        $this->assertTrue($this->invokeMethod('getConfig', array()) instanceof Expercash_Expercash_Model_Config);
    }

    public function testGetIframeUrl()
    {
        $checkoutSessionMock = $this->getModelMock('checkout/session', array('init', 'start'));
        $this->replaceByMock('model', 'checkout/session', $checkoutSessionMock);
        $iframeMock = $this->getModelMock('expercash/request_iframe', array('setOrderDataToSession'));
        $this->replaceByMock('model', 'expercash/request_iframe', $iframeMock);
        $this->assertEquals(
            $this->invokeMethod('getIframeUrl', array()), Mage::getModel('expercash/request_iframe')->getIframeUrl()
        );
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
        $object = new Expercash_Expercash_Block_Expercash();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
