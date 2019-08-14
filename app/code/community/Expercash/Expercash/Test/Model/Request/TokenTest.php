<?php


class Expercash_Expercash_Test_Model_Request_TokenTest extends EcomDev_PHPUnit_Test_Case_Config
{


    public function testCalcCentAmount()
    {
        $amount = 33.30;
        $centAmount = $this->invokeMethod('calcCentAmount', array($amount));
        $this->assertEquals(3330, $centAmount);
    }


    public function testGetConfig()
    {
        $this->assertTrue($this->invokeMethod('getConfig') instanceof Expercash_Expercash_Model_Config);
    }


    /**
     * @loadFixture  order
     */
    public function testGetTokenParamsWithoutBarzahlen()
    {

        $order = Mage::getModel('sales/order')->load(11);

        $coreUrlModel = $this->getModelMock('core/url', array('getUseSession'));
        $coreUrlModel->expects($this->any())
            ->method('getUseSession')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'core/url', $coreUrlModel);

        $coreSessionMock = $this->getModelMock('core/session', array('init', 'start'));
        $this->replaceByMock('model', 'core/session', $coreSessionMock);

        $configMock = $this->getModelMock(
            'expercash/config', array(
                'getPopupId',
                'getConfigData',
                'getProfilId'
            )
        );

        $configMock->expects($this->any())
            ->method('getPopupId')
            ->will($this->returnValue(1234));

        $configMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue('expercashcc'));

        $configMock->expects($this->any())
            ->method('getProfilId')
            ->will($this->returnValue(12));
        $this->replaceByMock('model', 'expercash/config', $configMock);

        $checkoutMock = $this->getModelMock(
            'checkout/session', array(
                'getLastRealOrderId',
                'start',
                'init'
            )
        );
        $checkoutMock->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(11));
        $this->replaceByMock('model', 'checkout/session', $checkoutMock);

        $modelMock = $this->getModelMock('expercash/request_token', array('calcCentAmount'));
        $modelMock->expects($this->any())
            ->method('calcCentAmount')
            ->will($this->returnValue(1190));

        $urlArray = $modelMock->getTokenParams($order);

        $this->assertTrue(array_key_exists('popupId', $urlArray));
        $this->assertTrue(array_key_exists('jobId', $urlArray));
        $this->assertTrue(array_key_exists('transactionId', $urlArray));
        $this->assertTrue(array_key_exists('amount', $urlArray));
        $this->assertTrue(array_key_exists('currency', $urlArray));
        $this->assertTrue(array_key_exists('paymentMethod', $urlArray));
        $this->assertTrue(array_key_exists('returnUrl', $urlArray));
        $this->assertTrue(array_key_exists('errorUrl', $urlArray));
        $this->assertTrue(array_key_exists('notifyUrl', $urlArray));
        $this->assertTrue(array_key_exists('profile', $urlArray));

        $this->assertEquals(1234, $urlArray['popupId']);
        $this->assertEquals(100000011, $urlArray['jobId']);
        $this->assertEquals(100000011, $urlArray['transactionId']);
        $this->assertEquals(1190, $urlArray['amount']);
        $this->assertEquals('EUR', $urlArray['currency']);
        $this->assertEquals('expercashcc', $urlArray['paymentMethod']);
        $this->assertEquals(
            Mage::getUrl('expercash/expercash/success', array('_secure' => true)), $urlArray['returnUrl']
        );
        $this->assertEquals(Mage::getUrl('expercash/expercash/error', array('_secure' => true)), $urlArray['errorUrl']);
        $this->assertEquals(
            Mage::getUrl('expercash/expercash/notify', array('_secure' => true)), $urlArray['notifyUrl']
        );
        $this->assertEquals(12, $urlArray['profile']);


    }

    /**
     * @loadFixture  order
     */
    public function testGetTokenParamsWithBarzahlen()
    {

        $order = Mage::getModel('sales/order')->load(12);

        $coreUrlModel = $this->getModelMock('core/url', array('getUseSession'));
        $coreUrlModel->expects($this->any())
            ->method('getUseSession')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'core/url', $coreUrlModel);

        $coreSessionMock = $this->getModelMock('core/session', array('init', 'start'));
        $this->replaceByMock('model', 'core/session', $coreSessionMock);

        $configMock = $this->getModelMock(
            'expercash/config', array(
                'getPopupId',
                'getConfigData',
                'getProfilId'
            )
        );

        $configMock->expects($this->any())
            ->method('getPopupId')
            ->will($this->returnValue(1234));

        $configMock->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue('expercashpc'));

        $configMock->expects($this->any())
            ->method('getProfilId')
            ->will($this->returnValue(12));
        $this->replaceByMock('model', 'expercash/config', $configMock);

        $checkoutMock = $this->getModelMock(
            'checkout/session', array(
                'getLastRealOrderId',
                'start',
                'init'
            )
        );
        $checkoutMock->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(11));
        $this->replaceByMock('model', 'checkout/session', $checkoutMock);

        $modelMock = $this->getModelMock('expercash/request_token', array('calcCentAmount'));
        $modelMock->expects($this->any())
            ->method('calcCentAmount')
            ->will($this->returnValue(1190));

        $urlArray = $modelMock->getTokenParams($order);
        $this->assertTrue(array_key_exists('customerPrename', $urlArray));
        $this->assertTrue(array_key_exists('customerName', $urlArray));
        $this->assertTrue(array_key_exists('customerAddress1', $urlArray));
        $this->assertTrue(array_key_exists('customerZip', $urlArray));
        $this->assertTrue(array_key_exists('customerCity', $urlArray));
        $this->assertTrue(array_key_exists('customerCountry', $urlArray));
        $this->assertTrue(array_key_exists('customerEmail', $urlArray));
        $this->assertTrue(array_key_exists('updateUrl', $urlArray));

        $this->assertEquals('Hubertus', $urlArray['customerPrename']);
        $this->assertEquals('Fürstenberg', $urlArray['customerName']);
        $this->assertEquals('An der Tabaksmühle 3a', $urlArray['customerAddress1']);
        $this->assertEquals('04229', $urlArray['customerZip']);
        $this->assertEquals('DE', $urlArray['customerCountry']);
        $this->assertEquals('hubertus.von.fuerstenberg@trash-mail.com', $urlArray['customerEmail']);
        $this->assertEquals(
            Mage::getUrl('expercash/expercash/notify', array('_secure' => true)), $urlArray['updateUrl']
        );


    }


    public function getTokenParams($session, $order, $paymentObject)
    {
        $config = $this->getConfig();
        $code = $paymentObject->getCode();
        $urlArray = Array(
            'popupId'       => $config->getPopupId($order->getStoreId()),
            'jobId'         => $session->getLastRealOrderId(),
            'transactionId' => $session->getLastRealOrderId(),
            'amount'        => $this->calcCentAmount($order->getGrandTotal()),
            'currency'      => $order->getOrderCurrencyCode(),
            'paymentMethod' => $config->getConfigData('paymenttype', $code, $order->getStoreId()),
            'returnUrl'     => Mage::getUrl(
                    'expercash/expercash/success', array('_secure' => true)
                ),
            'errorUrl'      => Mage::getUrl(
                    'expercash/expercash/error', array('_secure' => true)
                ),
            'notifyUrl'     => Mage::getUrl(
                    'expercash/expercash/notify', array('_secure' => true)
                ),

            'profile'       => $config->getProfilId($order->getStoreId()),
        );

        if ($paymentObject instanceof Expercash_Expercash_Model_Expercashpc) {
            $urlArray = array_merge($this->getBarzahlenParams($order));
        }
        return $urlArray;
    }

    /**
     * @loadFixture  order
     */
    public function testGetBarzahlenParams()
    {
        $order = Mage::getModel('sales/order')->load(11);

        $coreUrlModel = $this->getModelMock('core/url', array('getUrl'));
        $coreUrlModel->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue('www.test.de'));
        $this->replaceByMock('model', 'core/url', $coreUrlModel);

        $barzahlenParams = $this->invokeMethod('getBarzahlenParams', array($order));
        $this->assertTrue(array_key_exists('customerPrename', $barzahlenParams));
        $this->assertTrue(array_key_exists('customerName', $barzahlenParams));
        $this->assertTrue(array_key_exists('customerAddress1', $barzahlenParams));
        $this->assertTrue(array_key_exists('customerZip', $barzahlenParams));
        $this->assertTrue(array_key_exists('customerCity', $barzahlenParams));
        $this->assertTrue(array_key_exists('customerCountry', $barzahlenParams));
        $this->assertTrue(array_key_exists('customerEmail', $barzahlenParams));
        $this->assertTrue(array_key_exists('updateUrl', $barzahlenParams));

        $this->assertEquals('Hubertus', $barzahlenParams['customerPrename']);
        $this->assertEquals('Fürstenberg', $barzahlenParams['customerName']);
        $this->assertEquals('An der Tabaksmühle 3a', $barzahlenParams['customerAddress1']);
        $this->assertEquals('04229', $barzahlenParams['customerZip']);
        $this->assertEquals('DE', $barzahlenParams['customerCountry']);
        $this->assertEquals('hubertus.von.fuerstenberg@trash-mail.com', $barzahlenParams['customerEmail']);
        $this->assertEquals('www.test.de', $barzahlenParams['updateUrl']);
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
        $object = Mage::getModel('expercash/request_token');
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}