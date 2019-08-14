<?php

class Expercash_Expercash_Test_Model_ExpercashTest extends EcomDev_PHPUnit_Test_Case_Controller
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

    /**
     * if token is emtpy then a Mage_Checkout_Exception is thrown
     *
     * @loadFixture ../../../var/fixtures/orders.yaml
     *
     * @expectedException Mage_Checkout_Exception
     * @expectedExceptionMessage No Expercash token.
     */
    public function testAuthorizeGetTokenFailed()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $payment = $order->getPayment();
        $amount = $order->getBaseGrandTotal();
        $infoInstance = new Varien_Object();
        $infoInstance->setOrder($order);

        $requestMock = $this->getModelMock('expercash/request_token_iframe', array('getTokenParams'));
        $requestMock->expects($this->once())
            ->method('getTokenParams')
            ->will($this->returnValue(array()));
        $this->replaceByMock('model','expercash/request_token_iframe',$requestMock);

        $tokenModelMock = $this->getModelMock('expercash/api_api', array('getIframeToken'));
        $tokenModelMock->expects($this->once())
            ->method('getIframeToken')
            ->will($this->returnValue(null));
        $this->replaceByMock('model','expercash/api_api',$tokenModelMock);

        $paymentModelMock = $this->getModelMock('expercash/expercash', array('getInfoInstance', 'cancel'));
        $paymentModelMock->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($infoInstance));
        $paymentModelMock->expects($this->any())
            ->method('cancel')
            ->will($this->returnValue(null));
        $this->replaceByMock('model', 'expercash/expercash', $paymentModelMock);
        $paymentModelMock->authorize($payment, $amount);

    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Please select another payment method!
     */
    public function testAuthorizeGetTokenParamsFailed()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $payment = $order->getPayment();
        $amount = $order->getBaseGrandTotal();
        $infoInstance = new Varien_Object();
        $infoInstance->setOrder($order);

        $requestMock = $this->getModelMock('expercash/request_token_iframe', array('getTokenParams'));
        $requestMock->expects($this->any())
            ->method('getTokenParams')
            ->will($this->throwException(new Mage_Core_Exception('Please select another payment method!')));
        $this->replaceByMock('model','expercash/request_token_iframe',$requestMock);


        $paymentModelMock = $this->getModelMock('expercash/expercash', array('getInfoInstance', 'cancel'));
        $paymentModelMock->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($infoInstance));
        $paymentModelMock->expects($this->any())
            ->method('cancel')
            ->will($this->returnValue(null));
        $this->replaceByMock('model', 'expercash/expercash', $paymentModelMock);
        $paymentModelMock->authorize($payment, $amount);

    }

     /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     *
     */
    public function testAuthorizeGetTokenParamsSuccess()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $payment = $order->getPayment();
        $amount = $order->getBaseGrandTotal();
        $infoInstance = new Varien_Object();
        $infoInstance->setOrder($order);
        $checkoutSession = new Varien_Object();

        $tokenModelMock = $this->getModelMock('expercash/api_api', array('getIframeToken'));
        $tokenModelMock->expects($this->once())
            ->method('getIframeToken')
            ->will($this->returnValue('foo'));
        $this->replaceByMock('model','expercash/api_api',$tokenModelMock);

        $paymentModelMock = $this->getModelMock('expercash/expercash', array(
                'getInfoInstance',
                'cancel',
                'getCheckoutSession'
            )
        );
        $paymentModelMock->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($infoInstance));

        $paymentModelMock->expects($this->any())
            ->method('cancel')
            ->will($this->returnValue(null));

        $paymentModelMock->expects($this->any())
            ->method('getCheckoutSession')
            ->will($this->returnValue($checkoutSession));

        $this->replaceByMock('model', 'expercash/expercash', $paymentModelMock);
        $paymentModelMock->authorize($payment, $amount);
        $this->assertEquals('foo', $checkoutSession->getData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY));

    }

    /**
     * @test
     * test for getOrderPlaceRedirectUrl
     */
    public function testGetOrderPlaceRedirectUrl()
    {
        $url = Mage::getUrl('expercash/expercash/reset');
        $this->assertEquals(
            $url, Mage::getModel('expercash/expercash')->getOrderPlaceRedirectUrl()
        );
        $this->assertNotEquals(
            'foo', Mage::getModel('expercash/expercash')->getOrderPlaceRedirectUrl()
        );
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetAdditionalPaymentInfo()
    {
        //load order with fixture
        $order = Mage::getModel('sales/order')->load(11);

        // array to be returned by helper mock
        $paymentParams = array(
            'owner',
            'maskedPan',
            'cardScheme',
            'validThru',
            'panCountry',
            'ipCountry',
            'attemptsTotal',
            'attemptsSameAction',
            '3dsStatus'
        );

        // create a array with response params
        $responseParams = array(
            'owner' => 'Max Muster',
            'maskedPan' => 'xxxx.xxxx.xxxx.1234',
            'cardScheme' => 'VISA',
            'validThru' => '201905',
            'panCountry' => 'Germany',
            'ipCountry' => 'Belgien',
            'attemptsTotal' => '3',
            'attemptsSameAction' => '2',
            '3dsStatus' => '2'
        );

        // mock helper function getAdditionalPaymentParams
        $helperMock = $this->getHelperMock('expercash/data', array(
            'getAdditionalPaymentParams',
            )
        );

        $helperMock->expects($this->once())
            ->method('getAdditionalPaymentParams')
            ->will($this->returnValue($paymentParams));
        $this->replaceByMock('helper', 'expercash/data', $helperMock);

        Mage::getModel('expercash/Expercash')->setAdditionalPaymentInfo($order->getPayment(), $responseParams);

        // assertion that there is a array returned
        $this->assertTrue(is_array($order->getPayment()->getAdditionalInformation()));

        // assertion that each response param exist as key in the returned array
        foreach ($paymentParams as $key) {
            $this->assertTrue(array_key_exists($key, $order->getPayment()->getAdditionalInformation()));
        }

        // assertions that the values we permitted are the same that get returned
        $this->assertEquals('Max Muster',$order->getPayment()->getAdditionalInformation('owner'));
        $this->assertEquals('xxxx.xxxx.xxxx.1234',$order->getPayment()->getAdditionalInformation('maskedPan'));
        $this->assertEquals('VISA',$order->getPayment()->getAdditionalInformation('cardScheme'));
        $this->assertEquals('201905',$order->getPayment()->getAdditionalInformation('validThru'));
        $this->assertEquals('Germany',$order->getPayment()->getAdditionalInformation('panCountry'));
        $this->assertEquals('Belgien',$order->getPayment()->getAdditionalInformation('ipCountry'));
        $this->assertEquals('3',$order->getPayment()->getAdditionalInformation('attemptsTotal'));
        $this->assertEquals('2',$order->getPayment()->getAdditionalInformation('attemptsSameAction'));
        $this->assertEquals('2',$order->getPayment()->getAdditionalInformation('3dsStatus'));

    }


    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetAdditionalPaymentInfoMissingResponseParam()
    {
        //load order with fixture
        $order = Mage::getModel('sales/order')->load(11);

        // array to be returned by helper mock
        $paymentParams = array(
            'owner',
            'maskedPan',
            'cardScheme',
            'validThru',
            'panCountry',
            'ipCountry',
            'attemptsTotal',
            'attemptsSameAction',
            '3dsStatus'
        );

        // create a array with response params without cardScheme
        $responseParams = array(
            'owner' => 'Max Muster',
            'maskedPan' => 'xxxx.xxxx.xxxx.1234',
            'validThru' => '201905',
            'panCountry' => 'Germany',
            'ipCountry' => 'Belgien',
            'attemptsTotal' => '3',
            'attemptsSameAction' => '2',
            '3dsStatus' => '2'
        );

        // mock helper function getAdditionalPaymentParams
        $helperMock = $this->getHelperMock('expercash/data', array(
            'getAdditionalPaymentParams',
            )
        );

        $helperMock->expects($this->once())
            ->method('getAdditionalPaymentParams')
            ->will($this->returnValue($paymentParams));
        $this->replaceByMock('helper', 'expercash/data', $helperMock);

        Mage::getModel('expercash/Expercash')->setAdditionalPaymentInfo($order->getPayment(), $responseParams);

        // cardScheme is missing from responseParams
        $this->assertFalse(array_key_exists('cardScheme', $order->getPayment()->getAdditionalInformation()));

        // assertions that the values we permitted are the same that get returned
        $this->assertEquals('Max Muster',$order->getPayment()->getAdditionalInformation('owner'));
        $this->assertEquals('xxxx.xxxx.xxxx.1234',$order->getPayment()->getAdditionalInformation('maskedPan'));
        $this->assertEquals('201905',$order->getPayment()->getAdditionalInformation('validThru'));
        $this->assertEquals('Germany',$order->getPayment()->getAdditionalInformation('panCountry'));
        $this->assertEquals('Belgien',$order->getPayment()->getAdditionalInformation('ipCountry'));
        $this->assertEquals('3',$order->getPayment()->getAdditionalInformation('attemptsTotal'));
        $this->assertEquals('2',$order->getPayment()->getAdditionalInformation('attemptsSameAction'));
        $this->assertEquals('2',$order->getPayment()->getAdditionalInformation('3dsStatus'));
    }

     /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetAdditionalPaymentInfoMissingResponseParams()
    {
        //load order with fixture
        $order = Mage::getModel('sales/order')->load(11);

        // array to be returned by helper mock
        $paymentParams = array(
            'owner',
            'maskedPan',
            'cardScheme',
            'validThru',
            'panCountry',
            'ipCountry',
            'attemptsTotal',
            'attemptsSameAction',
            '3dsStatus'
        );

        // create a empty array for response
        $responseParams = array();

        // mock helper function getAdditionalPaymentParams
        $helperMock = $this->getHelperMock('expercash/data', array(
            'getAdditionalPaymentParams',
            )
        );

        $helperMock->expects($this->once())
            ->method('getAdditionalPaymentParams')
            ->will($this->returnValue($paymentParams));
        $this->replaceByMock('helper', 'expercash/data', $helperMock);

        Mage::getModel('expercash/Expercash')->setAdditionalPaymentInfo($order->getPayment(), $responseParams);

        //assertion that there is a array returned
        $this->assertTrue(is_array($order->getPayment()->getAdditionalInformation()));

        //assertion that the size of array is 0, since there where no response params
        $this->assertTrue(0 === sizeof($order->getPayment()->getAdditionalInformation()));
    }


    public function testRefund()
    {
        $payment = new Varien_Object();
        $amount = 19.90;
        Mage::getModel('expercash/expercash')->refund($payment, $amount);

        $this->assertEquals('Error in refunding the payment',$payment->getStatusDescription());

    }

    public function testCalcCentAmount()
    {
        $amount = 33.30;
        $centAmount = $this->invokeMethod('calcCentAmount', array($amount));
        $this->assertEquals(3330, $centAmount);
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
        $object = Mage::getModel('expercash/expercash');
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function testGetCheckoutSession()
    {
        $checkoutSession = $this->invokeMethod('getCheckoutSession');
        $this->assertTrue($checkoutSession instanceof Mage_Checkout_Model_Session);
    }
}
