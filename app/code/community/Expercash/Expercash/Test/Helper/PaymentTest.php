<?php

class Expercash_Expercash_Test_Helper_PaymentTest
    extends EcomDev_PHPUnit_Test_Case
{
    private $_helper;
    private $store;

    public function setUp()
    {
        parent::setup();
        $this->_helper = Mage::helper('expercash/payment');
        $this->store = Mage::app()->getStore(1)->load(1);
        $this->store->resetConfig();
    }

    /**
     * send no invoice mail if it is denied by configuration
     */
    public function testSendNoInvoiceToCustomerIfDenied()
    {
        $this->store->setConfig('payment/expercashcc/sendinvoicemail', 0);
        $this->assertFalse(
            (bool)Mage::getModel('expercash/config')->getConfigData(
                'sendinvoicemail', 'expercashcc'
            )
        );
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('expercashcc');
        $order = Mage::getModel('sales/order');
        $order->setPayment($payment);

        $sentInvoice = $this->getModelMock(
            'sales/order_invoice', array(
                'getEmailSent',
                'sendEmail',
                'getOrder'
            )
        );

        $sentInvoice->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));


        $sentInvoice->expects($this->any())
            ->method('getEmailSent')
            ->will($this->returnValue(false));

        $sentInvoice->expects($this->never())
            ->method('sendEmail');

        $this->_helper->sendInvoiceToCustomer($sentInvoice);
    }

    /**
     * send no invoice mail if it was already sent
     */
    public function testSendNoInvoiceToCustomerIfAlreadySent()
    {
        $this->store->setConfig('payment/expercashcc/sendinvoicemail', 1);
        $this->assertTrue(
            (bool)Mage::getModel('expercash/config')->getConfigData(
                'sendinvoicemail', 'expercashcc', $this->store->getId()
            )
        );
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('expercashcc');
        $order = Mage::getModel('sales/order');
        $order->setPayment($payment);

        $someInvoice = $this->getModelMock(
            'sales/order_invoice', array(
                'getEmailSent',
                'sendEmail',
                'getOrder'
            )
        );

        $someInvoice->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $someInvoice->expects($this->any())
            ->method('getEmailSent')
            ->will($this->returnValue(true));
        $someInvoice->expects($this->never())
            ->method('sendEmail');
        $this->_helper->sendInvoiceToCustomer($someInvoice);
    }

    /**
     * send invoice mail
     */
    public function testSendInvoiceToCustomerIfEnabled()
    {
        $this->store->setConfig('payment/expercashcc/sendinvoicemail', 1);
        $this->assertTrue(
            (bool)Mage::getModel('expercash/config')->getConfigData(
                'sendinvoicemail', 'expercashcc', $this->store->getId()
            )
        );
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('expercashcc');
        $order = Mage::getModel('sales/order');
        $order->setPayment($payment);


        $anotherInvoice = $this->getModelMock(
            'sales/order_invoice', array(
                'getEmailSent',
                'sendEmail',
                'getOrder'
            )
        );

        $anotherInvoice->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $anotherInvoice->expects($this->any())
            ->method('getEmailSent')
            ->will($this->returnValue(false));
        $anotherInvoice->expects($this->any())
            ->method('sendEmail')
            ->with($this->equalTo(true));
        $this->_helper->sendInvoiceToCustomer($anotherInvoice);
    }


    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Response is not correct.
     */
    public function testCheckReturnedDataResponseParamsWrong()
    {
        Mage::helper('expercash/payment')->checkReturnedData(
            array('test', 'test2')
        );

    }

    /**
     * @loadFixture              orders.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Response is not correct.
     */
    public function testCheckReturnedDataDependentParamMissing()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $this->store->setConfig('payment/expercashpc/active', 1);

        $paymentHelperMock = $this->getHelperMock(
            'expercash/payment', array('getOrder')
        );
        $paymentHelperMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this->replaceByMock('helper', 'expercash/payment', $paymentHelperMock);

        $paymentHelperMock->checkReturnedData($this->getResponseParams());
    }

    /**
     * @loadFixture     orders.yaml
     */
    public function testCheckReturnedDataSuccess()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $this->store->setConfig('payment/expercashpc/active', 1);

        $paymentHelperMock = $this->getHelperMock(
            'expercash/payment', array(
                'getOrder',
                'setExperCashData'
            )
        );
        $paymentHelperMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this->replaceByMock('helper', 'expercash/payment', $paymentHelperMock);

        $params = $this->getResponseParams();
        $params['paymentStatus'] = 'paid';
        $this->assertTrue($paymentHelperMock->checkReturnedData($params));
        $order = Mage::getModel('sales/order')->load(11);
        $this->assertEquals(1, $order->getEmailSent());


    }

    /**
     * @loadFixture     orders.yaml
     */
    public function testCheckReturnedDataFailed()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $this->store->setConfig('payment/expercashpc/active', 1);

        $paymentHelperMock = $this->getHelperMock(
            'expercash/payment', array(
                'getOrder',
                'setExperCashData',
                'getChecksum'
            )
        );
        $paymentHelperMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $paymentHelperMock->expects($this->any())
            ->method('getChecksum')
            ->will($this->returnValue('4711'));

        $params = $this->getResponseParams();
        $params['paymentStatus'] = 'paid';
        $this->assertFalse($paymentHelperMock->checkReturnedData($params));
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $this->assertTrue($order->isCanceled());


    }

    public function testValidateNonPcExpercashPayments()
    {
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('expercashcc');
        $payment->setAdditionalInformation('notifyActionCounter', 2);
        $this->assertFalse($this->invokeMethod('validateNonPcExpercashPayments', array($payment)));
        $payment->setMethod('expercashpc');
        $payment->setAdditionalInformation('notifyActionCounter', 1);
        $this->assertTrue($this->invokeMethod('validateNonPcExpercashPayments', array($payment)));

    }

    public function testValidateBarzahlenSecondCall()
    {
        $order = Mage::getModel('sales/order');
        $now = new DateTime();
        $date =  $now->sub(new DateInterval('P5D'))->format('Y-m-d');
        $order->setCreatedAt($date);
        $this->assertTrue($this->invokeMethod('validateBarzahlenSecondCall', array($order)));
        $date =  $now->sub(new DateInterval('P15D'))->format('Y-m-d');
        $order->setCreatedAt($date);
        $this->assertFalse($this->invokeMethod('validateBarzahlenSecondCall', array($order)));

    }


    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod($methodName, array $parameters = array()
    ) {
        $object = Mage::helper('expercash/payment');
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function testCheckResponseParams()
    {
        $params = array ('Foo' => 1);
        $this->assertFalse($this->invokeMethod('checkResponseParams',array($params)));
        $correctParams = $this->getResponseParams();
        $this->assertTrue($this->invokeMethod('checkResponseParams',array($correctParams)));
    }


    public function testCheckOrderDependentConditions()
    {

        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('foo');
        $order->setPayment($payment);
        $params = $this->getResponseParams();
        $this->assertTrue($this->invokeMethod('checkOrderDependentConditions', array($order,$params)));
        $payment->setMethod('expercashpc');
        $order->setPayment($payment);
        $this->assertFalse($this->invokeMethod('checkOrderDependentConditions', array($order,$params)));

    }

    public function testSetExpercashRef()
    {
        $order = Mage::getModel('sales/order');
        $order->setStoreId(0);
        $params = array('GuTID' => 1234);
        $order->setQuoteId(5);
        $modelMock = $this->getModelMock('expercash/expercashpc', array('getConfigData'));
        $modelMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Expercash_Expercash_Model_Expercashcc::PAYMENT_TYPE_CC_BUY));
        $this->replaceByMock('Model', 'expercash/expercashpc', $modelMock);

        $this->invokeMethod('setExpercashRef', array($params,$modelMock, $order));
        $experCashData = Mage::helper('expercash/payment')->getExperCashData(5);
//        $this->assertEquals('expercash_gutid',
//        );



    }

    /**
     * @loadFixture     orders.yaml
     */
    public function testCreateInvoiceForBarzahlen()
    {
        $order = Mage::getModel('sales/order')->load(11);

        $paymentHelperMock = $this->getHelperMock('expercash/payment', array('saveInvoice'));
        $paymentHelperMock->expects($this->any())
            ->method('saveInvoice')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'expercash/payment', $paymentHelperMock);

        $paymentModelMock = $this->getModelMock('expercash/expercashpc', array('getConfigData'));
        $paymentModelMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'expercash/expercashpc', $paymentModelMock);

        $response = $this->getResponseParams();
        $response['paymentStatus']  = 'PAID';

        $this->invokeMethod('createInvoice', array($paymentModelMock, $order, $response));
        $this->assertEquals(Mage_Sales_Model_Order::STATE_PROCESSING , $order->getState());




    }

       protected function getResponseParams()
    {
        return array(
            'transactionId' => 100000005,
            'amount'        => 3500,
            'currency'      => 'EUR',
            'paymentMethod' => 'expercashpc',
            'GuTID'         => 'CC1724106675000',
            'exportKey'     => '2dd0b4a21e99868e73b91865a2c3fb96',
        );
    }

}