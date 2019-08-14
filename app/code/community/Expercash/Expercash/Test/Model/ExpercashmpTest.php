<?php

class Expercash_Expercash_Test_Model_ExpercashmpTest extends EcomDev_PHPUnit_Test_Case_Controller
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
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals('expercashmp', $expercashmpf->getCode());
        $this->assertNotEquals('expercash', $expercashmpf->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(false, $expercashmpf->isGateway());
        $this->assertNotEquals(true, $expercashmpf->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(true, $expercashmpf->canAuthorize());
        $this->assertNotEquals(false, $expercashmpf->canAuthorize());
    }

    public function testExpercashCanCapturePartialProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(true, $expercashmpf->canCapturePartial());
        $this->assertNotEquals(false, $expercashmpf->canCapturePartial());
    }
    
    public function testExpercashCanRefundProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(false, $expercashmpf->canRefund());
        $this->assertNotEquals(true, $expercashmpf->canRefund());
    }
    
    public function testExpercashVoidProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashmpf->canVoid($payment));
        $this->assertNotEquals(true, $expercashmpf->canVoid($payment));
    }
    
    public function testExpercashUseInternalProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(false, $expercashmpf->canUseInternal());
        $this->assertNotEquals(true, $expercashmpf->canUseInternal());
    }
    
    public function testExpercashUseCheckoutProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(true, $expercashmpf->canUseCheckout());
        $this->assertNotEquals(false, $expercashmpf->canUseCheckout());
    }
    
    public function testExpercashUseMultishippingProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmp');
        $this->assertEquals(false, $expercashmpf->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashmpf->canUseForMultishipping());
    }
    
    /**
     * Test for canCapture in authorization mode
     */
    public function testCanCaptureAuthorize()
    {
        $expercashmpfMock = $this->getModelMock('expercash/expercashmp', array(
          'getExperCashInfo'  
            )
        );
        $expercashmpfMock->expects($this->once())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('masterpass_authorize'));
        
        $this->assertTrue($expercashmpfMock->canCapture());
   }
   
    /**
     * Test for canCapture in direct sale mode
     */
   public function testCanCaptureMpBuy()
    {
        $expercashmpfMock = $this->getModelMock('expercash/expercashmp', array(
          'getExperCashInfo'  
            )
        );
                
        $expercashmpfMock->expects($this->once())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('masterpass_buy'));
        
        $this->assertFalse($expercashmpfMock->canCapture());
   }

    public function testIsDirectSaleEnabled()
    {
        $expercashmpfMock = $this->getModelMock('expercash/expercashmp', array(
                'getConfigData'
            )
        );

        $expercashmpfMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashmp::PAYMENT_TYPE_MP_BUY));

        $this->assertTrue($expercashmpfMock->isDirectSaleEnabled());

        $expercashmpfMock = $this->getModelMock('expercash/expercashmp', array(
                'getConfigData'
            )
        );

        $expercashmpfMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashmp::PAYMENT_TYPE_MP_AUTH));

        $this->assertFalse($expercashmpfMock->isDirectSaleEnabled());
    }


}
