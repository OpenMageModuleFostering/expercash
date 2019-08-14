<?php

class Expercash_Expercash_Test_Model_ExpercashgpTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    public function setUp()
    {
        parent::setUp();
        $this->store = Mage::app()->getStore(0)->load(0);
    }

    public function testExpercashccCodeProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals('expercashgp', $expercashgp->getCode());
        $this->assertNotEquals('expercash', $expercashgp->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(false, $expercashgp->isGateway());
        $this->assertNotEquals(true, $expercashgp->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(true, $expercashgp->canAuthorize());
        $this->assertNotEquals(false, $expercashgp->canAuthorize());
    }

    public function testExpercashCanCaptureProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertFalse($expercashgp->canCapture());
        $this->assertNotEquals(true, $expercashgp->canCapture());

    }
    
    public function testExpercashCanCapturePartialProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(false, $expercashgp->canCapturePartial());
        $this->assertNotEquals(true, $expercashgp->canCapturePartial());
    }
    
    public function testExpercashCanRefundProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(false, $expercashgp->canRefund());
        $this->assertNotEquals(true, $expercashgp->canRefund());
    }
    
    public function testExpercashVoidProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashgp->canVoid($payment));
        $this->assertNotEquals(true, $expercashgp->canVoid($payment));
    }
    
    public function testExpercashUseInternalProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(false, $expercashgp->canUseInternal());
        $this->assertNotEquals(true, $expercashgp->canUseInternal());
    }
    
    public function testExpercashUseCheckoutProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(true, $expercashgp->canUseCheckout());
        $this->assertNotEquals(false, $expercashgp->canUseCheckout());
    }
    
    public function testExpercashUseMultishippingProperty()
    {
        $expercashgp = Mage::getModel('expercash/expercashgp');
        $this->assertEquals(false, $expercashgp->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashgp->canUseForMultishipping());
    }

    public function testExpercashGpDirectSaleEnabled()
    {
        $this->assertFalse(Mage::getModel('expercash/expercashgp')->isDirectSaleEnabled());

        $expercashgpMock = $this->getModelMock('expercash/expercashgp', array(
                'getConfigData'
            )
        );

        $expercashgpMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashgp::PAYMENT_TYPE_GP));
        $this->assertTrue($expercashgpMock->isDirectSaleEnabled());
    }
}
