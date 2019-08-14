<?php

class Expercash_Expercash_Test_Model_Request_IframeTest extends EcomDev_PHPUnit_Test_Case_Config
{

    /**
     * @loadFixture  order
     */
    public function testGetJobId()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $this->assertEquals(100000011, $this->invokeMethod('getJobId', array($order)));

    }


    public function testGetConfig()
    {
        $this->assertTrue($this->invokeMethod('getConfig', array()) instanceof Expercash_Expercash_Model_Config);
    }

    /**
     * @loadFixture  order
     */
    public function testGetOrder()
    {
        $session = new Varien_Object();
        $session->setLastOrderId(11);
        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array('getCheckoutSession'));
        $iframeModelMock->expects($this->any())
            ->method('getCheckoutSession')
            ->will($this->returnValue($session));

        $this->assertEquals(11, $iframeModelMock->getOrder()->getId());
        $this->assertTrue($iframeModelMock->getOrder() instanceof Mage_Sales_Model_Order);

    }

    /**
     * @loadFixture  order
     */
    public function testGetStoreId()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array('getOrder'));
        $iframeModelMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'expercash/request_iframe', $iframeModelMock);
        $this->assertEquals(1, $this->invokeMethod('getStoreId'));

    }

    public function testGetCoreUrlHelper()
    {
        $this->assertTrue($this->invokeMethod('getCoreUrlHelper', array()) instanceof Mage_Core_Helper_Url);
    }



    public function testGetCheckoutSession()
    {
        $coreSessionMock = $this->getModelMock('checkout/session', array('init', 'start'));
        $this->replaceByMock('model', 'checkout/session', $coreSessionMock);

        $this->assertTrue($this->invokeMethod('getCheckoutSession', array()) instanceof $coreSessionMock);
    }


    public function testGetTokenSessionId()
    {
        $session = new Varien_Object();
        $session->setData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY,11);

        $coreSessionMock = $this->getModelMock('core/session', array('init', 'start'));
        $this->replaceByMock('model', 'core/session', $coreSessionMock);



        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array('getCheckoutSession'));
        $iframeModelMock->expects($this->any())
            ->method('getCheckoutSession')
            ->will($this->returnValue($session));
        $this->replaceByMock('model', 'expercash/request_iframe', $iframeModelMock);

        $this->assertEquals(11, $this->invokeMethod('getTokenSessionId'));

    }

    /**
     * @loadFixture  order
     */
    public function testSetOrderDataToSession()
    {

        $session = new Varien_Object();

        $helperMock = $this->getHelperMock('expercash/payment', array('createExperCashOrderId'));
        $helperMock->expects($this->once())
            ->method('createExperCashOrderId')
            ->will($this->returnValue('0816'));
        $this->replaceByMock('helper', 'expercash/payment', $helperMock);

        $session->setLastRealOrderId('0815');
        $session->setLastOrderId(11);
        $session->setQuoteId(666);

        $coreSessionMock = $this->getModelMock('core/session', array('init', 'start'));
        $this->replaceByMock('model', 'core/session', $coreSessionMock);

        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array('getCheckoutSession'));
        $iframeModelMock->expects($this->any())
            ->method('getCheckoutSession')
            ->will($this->returnValue($session));
        $iframeModelMock->setOrderDataToSession();
        $this->assertEquals('0816', $session->getExpercashOrderId());
        $this->assertEquals($session->getLastRealOrderId() ,$session->getExpercashRealOrderId());
        $this->assertEquals($session->getQuoteId() ,$session->getExpercashQuoteId());
    }


    /**
     * @loadFixture  order
     */
    public function testGetIframeParams()
    {
        $order = Mage::getModel('sales/order')->load(11);

        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array(
                'getTokenSessionId',
                'buildSessionKeyHash'
            )
        );
        $iframeModelMock->expects($this->any())
            ->method('getTokenSessionId')
            ->will($this->returnValue('abcd123'));

        $iframeModelMock->expects($this->any())
            ->method('buildSessionKeyHash')
            ->will($this->returnValue('123abc'));

        $params = $iframeModelMock->getIframeParams($order);

        $this->assertTrue(array_key_exists('preparedSession',$params));
        $this->assertTrue(array_key_exists('sessionKey',$params));

        $this->assertEquals('abcd123',$params['preparedSession']);
        $this->assertEquals('123abc',$params['sessionKey']);
    }

    /**
     * @loadFixture  order
     */
    public function testBuildSessionKeyHash()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $sessionKeyHashTest = sha1('abcd123'.'15'.'foo');

        $configModelMock = $this->getModelMock('expercash/config', array('getAuthorizationkey'));
        $configModelMock->expects($this->any())
            ->method('getAuthorizationkey')
            ->will($this->returnValue('foo'));
        $this->replaceByMock('model', 'expercash/config', $configModelMock);

        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array(
                'getTokenSessionId',
                'getJobId'
            )
        );
        $iframeModelMock->expects($this->any())
            ->method('getTokenSessionId')
            ->will($this->returnValue('abcd123'));


        $iframeModelMock->expects($this->any())
            ->method('getJobId')
            ->will($this->returnValue('15'));

        $this->assertEquals($sessionKeyHashTest,$iframeModelMock->buildSessionKeyHash($order));

    }

    public function testGetIframeUrl()
    {
        $iframeParams = array('foo' => '123','baar'=> '456');
        $checkoutSession = new Varien_Object();
        $checkoutSession->setData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY,'foo');

        $coreSessionMock = $this->getModelMock('core/session', array('init', 'start'));
        $this->replaceByMock('model', 'core/session', $coreSessionMock);

        $configModelMock = $this->getModelMock('expercash/config', array('getIframeUrl'));
        $configModelMock->expects($this->any())
            ->method('getIframeUrl')
            ->will($this->returnValue('https://www.test.de'));
        $this->replaceByMock('model', 'expercash/config', $configModelMock);


        $iframeModelMock = $this->getModelMock('expercash/request_iframe', array(
                'getIframeParams',
                'getCoreUrlHelper',
                'getCheckoutSession'
            )
        );

        $iframeModelMock->expects($this->any())
            ->method('getIframeParams')
            ->will($this->returnValue($iframeParams));

        $iframeModelMock->expects($this->any())
            ->method('getCoreUrlHelper')
            ->will($this->returnValue(Mage::helper('core/url')));

        $iframeModelMock->expects($this->any())
            ->method('getCheckoutSession')
            ->will($this->returnValue($checkoutSession));

        $this->assertEquals('https://www.test.de?foo=123&baar=456',$iframeModelMock->getIframeUrl());
        $this->assertNotEquals('foo',$checkoutSession->getData(Expercash_Expercash_Model_Config::TOKEN_REGISTRY_KEY));

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
        $object = Mage::getModel('expercash/request_iframe');
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }


}