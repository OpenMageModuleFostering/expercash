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
class Expercash_Expercash_Test_Block_Masterpass_FormTest extends EcomDev_PHPUnit_Test_Case
{



    public function testGetMethodName()
    {
        $block = new Expercash_Expercash_Block_Masterpass_Form();

        $this->assertEquals('expercashmp', $block->getMethodCode());
    }

    public function testGetMasterpassConfig()
    {
        $configModel = $this->invokeMethod('getMasterpassConfig');
        $this->assertTrue($configModel instanceof Expercash_Expercash_Model_Masterpass_Config);
    }

    public function testHandleLogoDisplayFalse()
    {
        $configModelMock = $this->getModelMock('expercash/masterpass_config', array(
                'showMasterpassLogoInCheckout',
            )
        );
        $configModelMock->expects($this->any())
            ->method('showMasterpassLogoInCheckout')
            ->will($this->returnValue(false));

        $this->replaceByMock('model', 'expercash/masterpass_config', $configModelMock);

        $this->assertEquals(null, $this->invokeMethod('handleLogoDisplay'));
    }


    public function testHandleLogoDisplayTrue()
    {
//        $this->markTestIncomplete();

        // needs fix , mocking block singleton doesnt work somehow

        $configModelMock = $this->getModelMock('expercash/masterpass_config', array(
                'showMasterpassLogoInCheckout',
            )
        );
        $configModelMock->expects($this->any())
            ->method('showMasterpassLogoInCheckout')
            ->will($this->returnValue(true));

        $this->replaceByMock('model', 'expercash/masterpass_config', $configModelMock);

        $markMock = $this->getBlockMock('core/template', array(
                'setPaymentAcceptanceMarkHref',
                'setPaymentAcceptanceMarkSrc'
            )
        );

        $markMock->expects($this->any())
            ->method('setPaymentAcceptanceMarkHref')
            ->will($this->returnSelf());

        $this->replaceByMock('block', 'core/template', $markMock);

        $this->assertInstanceOf(get_class($markMock), $this->invokeMethod('handleLogoDisplay'));
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
        $object = new Expercash_Expercash_Block_Masterpass_Form();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }


}
