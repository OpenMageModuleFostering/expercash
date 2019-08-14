<?php

class Expercash_Expercash_Test_Model_ExpercashmpfTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    public function setUp()
    {
        parent::setUp();
        $this->config   = Mage::getModel('expercash/config');
        $this->store    = Mage::app()->getStore(0)->load(1);
    }

    public function testExpercashccCodeProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals('expercashmpf', $expercashmpf->getCode());
        $this->assertNotEquals('expercash', $expercashmpf->getCode());
    }

    public function testExpercashIsGatewayProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(false, $expercashmpf->isGateway());
        $this->assertNotEquals(true, $expercashmpf->isGateway());
    }

    public function testExpercashCanAuthorizeProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(true, $expercashmpf->canAuthorize());
        $this->assertNotEquals(false, $expercashmpf->canAuthorize());
    }

    public function testExpercashCanCapturePartialProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(true, $expercashmpf->canCapturePartial());
        $this->assertNotEquals(false, $expercashmpf->canCapturePartial());
    }

    public function testExpercashCanRefundProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(false, $expercashmpf->canRefund());
        $this->assertNotEquals(true, $expercashmpf->canRefund());
    }

    public function testExpercashVoidProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $payment = new Varien_Object();
        $this->assertEquals(false, $expercashmpf->canVoid($payment));
        $this->assertNotEquals(true, $expercashmpf->canVoid($payment));
    }

    public function testExpercashUseInternalProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(false, $expercashmpf->canUseInternal());
        $this->assertNotEquals(true, $expercashmpf->canUseInternal());
    }

    public function testExpercashUseCheckoutProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(true, $expercashmpf->canUseCheckout());
        $this->assertNotEquals(false, $expercashmpf->canUseCheckout());
    }

    public function testExpercashUseMultishippingProperty()
    {
        $expercashmpf = Mage::getModel('expercash/expercashmpf');
        $this->assertEquals(false, $expercashmpf->canUseForMultishipping());
        $this->assertNotEquals(true, $expercashmpf->canUseForMultishipping());
    }

    /**
     * Test for canCapture in authorization mode
     */
    public function testCanCaptureAuthorize()
    {
        $expercashmpfMock = $this->getModelMock('expercash/expercashmpf', array(
            'getExperCashInfo'
        ));

        $expercashmpfMock->expects($this->any())
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
        $expercashmpfMock = $this->getModelMock('expercash/expercashmpf', array(
            'getExperCashInfo'
        ));

        $expercashmpfMock->expects($this->any())
           ->method('getExperCashInfo')
           ->with($this->equalTo('expercash_request_type'))
           ->will($this->returnValue('masterpass_buy'));

        $this->assertTrue($expercashmpfMock->canCapture());
   }

    /**
     * Check if payment model enables direct depending on given payment type.
     */
    public function testIsDirectSaleEnabled()
    {
        $mock = $this->getModelMock('expercash/expercashmpf', array('getConfigData'));
        $mock
            ->expects($this->any())
            ->method('getConfigData')
            ->with($this->equalTo('payment_action'))
            ->will($this->onConsecutiveCalls(
                Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE
            ))
        ;
        $this->replaceByMock('model', 'expercash/expercashmpf', $mock);


        $payment = Mage::getModel('expercash/expercashmpf');

        $this->assertTrue($payment->isDirectSaleEnabled());
        $this->assertFalse($payment->isDirectSaleEnabled());
    }

    /**
     * @loadFixture quote.yaml
     *
     */
    public function testIsAvailable()
    {
        $customerSession = $this->getModelMock(
            'customer/session', array('init', 'save')
        );
        $this->replaceByMock('model', 'customer/session', $customerSession);
        $quote = Mage::getModel('sales/quote')->load(1);
        $this->store->setConfig('payment/expercashmpf/active', 1);
        $this->assertTrue(Mage::getModel('expercash/expercashmpf')->isAvailable($quote));

        $quote = Mage::getModel('sales/quote')->load(2);
        $this->store->resetConfig();
        $this->store->setConfig('payment/expercashmpf/active', 0);

        $this->assertFalse(Mage::getModel('expercash/expercashmpf')->isAvailable($quote));


        $quote = Mage::getModel('sales/quote')->load(1);
        $this->store->setConfig('payment/expercashmpf/active', 1);
        $this->assertTrue(Mage::getModel('expercash/expercashmpf')->isAvailable($quote));

        $mock = $this->getModelMock('expercash/expercashmpf', array('getCurrenciesArray'));
        $mock
            ->expects($this->any())
            ->method('getCurrenciesArray')
            ->will($this->returnValue(array('foo')));
        $this->replaceByMock('model', 'expercash/expercashmpf', $mock);

        $this->assertFalse(Mage::getModel('expercash/expercashmpf')->isAvailable($quote));

    }

    public function testValidate()
    {
        $order = Mage::getModel('sales/order');
        $quote = Mage::getModel('sales/quote');
        $quote->getPayment()->setMethod('expercashmpf');
        $billindAddress = Mage::getModel('sales/order_address');
        $billindAddress->setCountryId(1299);
        $order->setBillingAddress($billindAddress);
        $infoInstance = Mage::getModel('sales/order_payment');
        $infoInstance->setOrder($order);
        $infoInstance->setQuote($quote);



        $mock = $this->getModelMock('expercash/expercashmpf', array('canUseForCountry'));
        $mock
            ->expects($this->any())
            ->method('canUseForCountry')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'expercash/expercashmpf', $mock);

        $mock->setInfoInstance($infoInstance);
        $mock->validate();


        $this->assertNotEquals('expercashmpf', $quote->getPayment()->getMethod());
        $this->assertEquals(null, $quote->getPayment()->getMethod());

    }
}
