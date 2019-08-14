<?php


class Expercash_Expercash_Test_Model_Api_CaptureTest extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @loadFixture orders
     */
    public function testCapture()
    {
        $this->markTestIncomplete("No assertion given.");

        $order = Mage::getModel('sales/order')->load(11);
        $payment = $order->getPayment();
        $amount = 100;
        $capture_type = 'expercashcc';

        $urlArray = Array(
                "pid" =>  1234,
                "pkey" => 52345,
                "cref" => 123456,
                'amount' => round($amount * 100),
                "action" => 'cc_capture',
                "reference" => 55555
            );

       $captureMock = $this->getModelMock('expercash/api_capture',array(
           '_postRequest',
           'validateAndSaveResponse'
          )
       );
       $captureMock->expects($this->once())
                   ->method('_postRequest');

       $captureMock->expects($this->once())
                   ->method('validateAndSaveResponse');

       $this->replaceByMock('model', 'expercash/api_capture', $captureMock);
       $captureMock->capture($payment, $amount, $capture_type);
    }


    /**
     * @loadFixture orders
     */
    public function testSaveExperCashData()
    {
        // can test this method yet because setExperCashData in the payment helper
        // does an update on the database
        $this->markTestIncomplete();
    }

     /**
     * @loadFixture ../../../../var/fixtures/orders
     */
    public function testValidateAndSaveResponse()
    {
        $quote = Mage::getModel('sales/order')->load(11);
        $payment = $quote->getPayment();
        $response = $response = new Varien_Object();
        $response->rc = '100';

        $captureModelMock = $this->getModelMock('expercash/api_capture', array(
            'parseResponse',
            'saveExperCashData',
            'saveOrderHistory'
            )
        );

        $captureModelMock->expects($this->once())
                   ->method('parseResponse')
                   ->will($this->returnValue($response));

        $captureModelMock->validateAndSaveResponse($response, $payment);
        $this->assertEquals(Expercash_Expercash_Model_Expercash::STATUS_SUCCESS, $payment->getStatus());

    }


    /**
     * @loadFixture ../../../../var/fixtures/orders
     */
    public function testValidateAndSaveResponseWithInvalidResponse()
    {
        $quote = Mage::getModel('sales/order')->load(11);
        $payment = $quote->getPayment();
        $response = $response = new Varien_Object();
        $response->rc = '101';

        $captureModelMock = $this->getModelMock('expercash/api_capture', array(
            'parseResponse',
            'saveExperCashData',
            'saveOrderHistory'
            )
        );

        $captureModelMock->expects($this->once())
                   ->method('parseResponse')
                   ->will($this->returnValue($response));
        $this->setExpectedException('Mage_Core_Exception');
        $captureModelMock->validateAndSaveResponse($response, $payment);
    }

     /**
     *  @loadFixture ../../../../var/fixtures/orders
     */
    public function testValidateAndSaveResponseThrowsException()
    {
        $quote = Mage::getModel('sales/order')->load(11);
        $payment = $quote->getPayment();
        $response = $response = new Varien_Object();
        $response->rc = '101';

        $captureModelMock = $this->getModelMock('expercash/api_capture', array(
            'parseResponse',
            'saveExperCashData',
            'saveOrderHistory'
            )
        );
        $exception = new Mage_Core_Exception();
        $exception->setMessage('test message');
        $captureModelMock->expects($this->once())
                   ->method('parseResponse')
                   ->will($this->throwException($exception));
        $message = '';
        try {
            $captureModelMock->validateAndSaveResponse($response, $payment);
        } catch (Exception $e) {
            $this->assertEquals(
                Mage::helper('expercash/data')->__(
                    'Error during capture: %s',
                    $exception->getMessage()
                ),
                $e->getMessage()
            );
        }

    }
}

