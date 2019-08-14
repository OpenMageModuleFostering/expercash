<?php

class Expercash_Expercash_Test_Model_ExpercashppTest extends EcomDev_PHPUnit_Test_Case_Controller
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
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals('expercashpp', $expercashpp->getCode());
        $this->assertNotEquals('expercash', $expercashpp->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(false, $expercashpp->isGateway());
        $this->assertNotEquals(true, $expercashpp->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(true, $expercashpp->canAuthorize());
        $this->assertNotEquals(false, $expercashpp->canAuthorize());
    }

    public function testExpercashCanCaptureProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertFalse($expercashpp->canCapture());
        $this->assertNotEquals(true, $expercashpp->canCapture());
    }
    
    public function testExpercashCanCapturePartialProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(false, $expercashpp->canCapturePartial());
        $this->assertNotEquals(true, $expercashpp->canCapturePartial());
    }
    
    public function testExpercashCanRefundProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(false, $expercashpp->canRefund());
        $this->assertNotEquals(true, $expercashpp->canRefund());
    }
    
    public function testExpercashVoidProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashpp->canVoid($payment));
        $this->assertNotEquals(true, $expercashpp->canVoid($payment));
    }
    
    public function testExpercashUseInternalProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(false, $expercashpp->canUseInternal());
        $this->assertNotEquals(true, $expercashpp->canUseInternal());
    }
    
    public function testExpercashUseCheckoutProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(true, $expercashpp->canUseCheckout());
        $this->assertNotEquals(false, $expercashpp->canUseCheckout());
    }
    
    public function testExpercashUseMultishippingProperty()
    {
        $expercashpp = Mage::getModel('expercash/expercashpp');
        $this->assertEquals(false, $expercashpp->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashpp->canUseForMultishipping());
    }
}
