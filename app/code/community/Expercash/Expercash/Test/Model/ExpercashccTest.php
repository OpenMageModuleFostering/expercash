<?php

class Expercash_Expercash_Test_Model_ExpercashccTest extends EcomDev_PHPUnit_Test_Case_Controller
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
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals('expercashcc', $expercashcc->getCode());
        $this->assertNotEquals('expercash', $expercashcc->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(false, $expercashcc->isGateway());
        $this->assertNotEquals(true, $expercashcc->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(true, $expercashcc->canAuthorize());
        $this->assertNotEquals(false, $expercashcc->canAuthorize());
    }

    public function testExpercashCanCapturePartialProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(true, $expercashcc->canCapturePartial());
        $this->assertNotEquals(false, $expercashcc->canCapturePartial());
    }
    
    public function testExpercashCanRefundProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(false, $expercashcc->canRefund());
        $this->assertNotEquals(true, $expercashcc->canRefund());
    }
    
    public function testExpercashVoidProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashcc->canVoid($payment));
        $this->assertNotEquals(true, $expercashcc->canVoid($payment));
    }
    
    public function testExpercashUseInternalProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(false, $expercashcc->canUseInternal());
        $this->assertNotEquals(true, $expercashcc->canUseInternal());
    }
    
    public function testExpercashUseCheckoutProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(true, $expercashcc->canUseCheckout());
        $this->assertNotEquals(false, $expercashcc->canUseCheckout());
    }
    
    public function testExpercashUseMultishippingProperty()
    {
        $expercashcc = Mage::getModel('expercash/expercashcc');
        $this->assertEquals(false, $expercashcc->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashcc->canUseForMultishipping());
    }
    
    /**
     * Test for canCapture in authorization mode
     */
    public function testCanCaptureAuthorize()
    {
        $expercashccMock = $this->getModelMock('expercash/expercashcc', array(
          'getExperCashInfo'  
            )
        );
        $expercashccMock->expects($this->once())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('cc_authorize'));
        
        $this->assertTrue($expercashccMock->canCapture());
   }
   
    /**
     * Test for canCapture in direct sale mode
     */
   public function testCanCaptureCCBuy()
    {
        $expercashccMock = $this->getModelMock('expercash/expercashcc', array(
          'getExperCashInfo'  
            )
        );
                
        $expercashccMock->expects($this->once())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('cc_buy'));
        
        $this->assertFalse($expercashccMock->canCapture());
   }

    public function testIsDirectSaleEnabled()
    {
        $expercashccMock = $this->getModelMock('expercash/expercashcc', array(
                'getConfigData'
            )
        );

        $expercashccMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashcc::PAYMENT_TYPE_CC_BUY));

        $this->assertTrue($expercashccMock->isDirectSaleEnabled());

        $expercashccMock = $this->getModelMock('expercash/expercashcc', array(
                'getConfigData'
            )
        );

        $expercashccMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashcc::PAYMENT_TYPE_CC_AUTH));

        $this->assertFalse($expercashccMock->isDirectSaleEnabled());
    }


}
