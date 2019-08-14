<?php

class Expercash_Expercash_Test_Model_ExpercashelvTest extends EcomDev_PHPUnit_Test_Case
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

    public function testExpercashelvCodeProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals('expercashelv', $expercashelv->getCode());
        $this->assertNotEquals('expercash', $expercashelv->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(true, $expercashelv->isGateway());
        $this->assertNotEquals(false, $expercashelv->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(true, $expercashelv->canAuthorize());
        $this->assertNotEquals(false, $expercashelv->canAuthorize());
    }

    public function testExpercashCanCapturePartialProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(false, $expercashelv->canCapturePartial());
        $this->assertNotEquals(true, $expercashelv->canCapturePartial());
    }

    public function testExpercashCanRefundProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(false, $expercashelv->canRefund());
        $this->assertNotEquals(true, $expercashelv->canRefund());
    }

    public function testExpercashVoidProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashelv->canVoid($payment));
        $this->assertNotEquals(true, $expercashelv->canVoid($payment));
    }

    public function testExpercashUseInternalProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(false, $expercashelv->canUseInternal());
        $this->assertNotEquals(true, $expercashelv->canUseInternal());
    }

    public function testExpercashUseCheckoutProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(true, $expercashelv->canUseCheckout());
        $this->assertNotEquals(false, $expercashelv->canUseCheckout());
    }

    public function testExpercashUseMultishippingProperty()
    {
        $expercashelv = Mage::getModel('expercash/expercashelv');
        $this->assertEquals(false, $expercashelv->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashelv->canUseForMultishipping());
    }

    /**
     * Test for canCapture in authorization mode
     */
    public function testCanCaptureElvAuthorize()
    {
        $expercashccMock = $this->getModelMock('expercash/expercashelv', array(
          'getExperCashInfo'
            )
        );
        $expercashccMock->expects($this->once())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('elv_authorize'));

        $this->assertTrue($expercashccMock->canCapture());
   }

    /**
     * Test for canCapture in direct sale mode
     */
   public function testCanCaptureElvBuy()
    {
        $expercashelvMock = $this->getModelMock('expercash/expercashelv', array(
          'getExperCashInfo'
            )
        );

        $expercashelvMock->expects($this->once())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('elv_buy'));

        $this->assertFalse($expercashelvMock->canCapture());
   }

    public function testIsDirectSaleEnabled()
    {
        $expercashelvMock = $this->getModelMock('expercash/expercashelv', array(
                'getConfigData'
            )
        );

        $expercashelvMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashelv::PAYMENT_TYPE_ELV_BUY));

        $this->assertTrue($expercashelvMock->isDirectSaleEnabled());

        $expercashelvMock = $this->getModelMock('expercash/expercashelv', array(
                'getConfigData'
            )
        );

        $expercashelvMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashelv::PAYMENT_TYPE_ELV_AUTH));

        $this->assertFalse($expercashelvMock->isDirectSaleEnabled());




    }
}
