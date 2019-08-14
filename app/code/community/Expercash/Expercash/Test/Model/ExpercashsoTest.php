<?php

class Expercash_Expercash_Test_Model_ExpercashsoTest extends EcomDev_PHPUnit_Test_Case_Controller
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
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals('expercashso', $expercashso->getCode());
        $this->assertNotEquals('expercash', $expercashso->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals(false, $expercashso->isGateway());
        $this->assertNotEquals(true, $expercashso->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals(true, $expercashso->canAuthorize());
        $this->assertNotEquals(false, $expercashso->canAuthorize());
    }

    public function testExpercashCanCaptureProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertFalse($expercashso->canCapture());
        $this->assertNotEquals(true, $expercashso->canCapture());
    }
    
    public function testExpercashCanCapturePartialProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals(false, $expercashso->canCapturePartial());
        $this->assertNotEquals(true, $expercashso->canCapturePartial());
    }
    
    public function testExpercashCanRefundProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals(false, $expercashso->canRefund());
        $this->assertNotEquals(true, $expercashso->canRefund());
    }
    
    public function testExpercashVoidProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashso->canVoid($payment));
        $this->assertNotEquals(true, $expercashso->canVoid($payment));
    }
    
    public function testExpercashUseInternalProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals(false, $expercashso->canUseInternal());
        $this->assertNotEquals(true, $expercashso->canUseInternal());
    }
    
    public function testExpercashUseCheckoutProperty()
    {
        $expercashso = Mage::getModel('expercash/expercashso');
        $this->assertEquals(true, $expercashso->canUseCheckout());
        $this->assertNotEquals(false, $expercashso->canUseCheckout());
    }
    
    public function testExpercashUseMultishippingProperty()
    {
        $expercashso = $this->getPaymentMethod();
        $this->assertEquals(false, $expercashso->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashso->canUseForMultishipping());
    }

    public function testExpercashSoDirectSaleEnabled()
    {
        $this->assertFalse($this->getPaymentMethod()->isDirectSaleEnabled());

        $expercashsoMock = $this->getModelMock('expercash/expercashso', array(
                'getConfigData'
            )
        );

        $expercashsoMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashso::PAYMENT_TYPE_SO));
        $this->assertTrue($expercashsoMock->isDirectSaleEnabled());
    }

    /**
     * @return Expercash_Expercash_Model_Expercashso
     */
    protected function getPaymentMethod()
    {
        return Mage::getModel('expercash/expercashso');
    }
}
