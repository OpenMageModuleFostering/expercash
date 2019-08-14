<?php
class Expercash_Expercash_Test_Model_Api_AbstractTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function test_PostRequest()
    {
        $configModelMock = $this->getModelMock('expercash/config',array(
            'getEpiUrl'
            )
        );
        $configModelMock->expects($this->once())
           ->method('getEpiUrl')
           ->will($this->returnValue('http://test.gateway.com'));
        $this->replaceByMock('model', 'expercash/config', $configModelMock);
        
        $urlArray = Array(
                "pid" => 11,
                "pkey" => 12,
                "cref" => 123456,
                'amount' => 45000,
                "action" => 'cc_authorize',
                "reference" => 55555
            );
        
        $fakeResponse = new Varien_Object();
        $fakeResponse->setBody('test me');
        $fakeClient = $this->getMock('Varien_Http_Client', array('request'));
        $fakeClient->expects($this->any())
                   ->method('request')
                   ->will($this->returnValue($fakeResponse));
        
        $clientMock = $this->getModelMock('expercash/api_capture', array(
            'getClient'
            )
        );
        $clientMock->expects($this->once())
           ->method('getClient')
           ->will($this->returnValue($fakeClient));
        $this->replaceByMock('model', 'expercash/api_capture', $clientMock);
        
        $reflectionClass = new ReflectionClass('Expercash_Expercash_Model_Api_Capture');
        $method = $reflectionClass->getMethod('_postRequest');
        $method->setAccessible(true);
        $captureClass = Mage::getModel('expercash/api_capture');
        
        $this->assertEquals('test me', $method->invoke($captureClass, $urlArray));
    }
    
    
    public function test_PostRequestWithException()
    {
        $configModelMock = $this->getModelMock('expercash/config',array(
            'getEpiUrl'
            )
        );
        $configModelMock->expects($this->once())
           ->method('getEpiUrl')
           ->will($this->returnValue('http://test.gateway.com'));
        $this->replaceByMock('model', 'expercash/config', $configModelMock);
        
        $urlArray = Array(
                "pid" => 11,
                "pkey" => 12,
                "cref" => 123456,
                'amount' => 45000,
                "action" => 'cc_authorize',
                "reference" => 55555
            );
        
        $fakeResponse = new Varien_Object();
        $fakeResponse->setBody('test me');
        $fakeClient = $this->getMock('Varien_Http_Client', array('request'));
        $fakeClient->expects($this->any())
                   ->method('request')
                   ->will($this->throwException(new Mage_Core_Exception()));
        
        $clientMock = $this->getModelMock('expercash/api_capture', array(
            'getClient'
            )
        );
        $clientMock->expects($this->once())
           ->method('getClient')
           ->will($this->returnValue($fakeClient));
        $this->replaceByMock('model', 'expercash/api_capture', $clientMock);
                
        $reflectionClass = new ReflectionClass('Expercash_Expercash_Model_Api_Capture');
        $method = $reflectionClass->getMethod('_postRequest');
        $method->setAccessible(true);
        $captureClass = Mage::getModel('expercash/api_capture');
        
        // assertion that a Mage_Core_Exception is thrown
        $this->setExpectedException('Mage_Core_Exception');

        $method->invoke($captureClass, $urlArray);
    }

    public function testParseResponse()
    {
         $bodyText = '<?xml version="1.0" encoding="iso-8859-1"?>
                     <!DOCTYPE epixml PUBLIC "easyDebit/epi/DTD" "https://epi.expercash.net/epi.dtd">

                     <epixml>
                        <rc>100</rc>
                        <rctext>Transaction successfull</rctext>
                        <timestamp>2013-04-15 15:47:18</timestamp>
                        <taid>00000000000256943810</taid>
                        <epi_payment_id>CC1752647744000</epi_payment_id>
                      </epixml>';

        $captureModel = Mage::getModel('expercash/api_capture');
        $simpleXML = simplexml_load_string($bodyText);
        $this->assertEquals($simpleXML, $captureModel->parseResponse($bodyText));
        $this->assertNotEquals($bodyText, $captureModel->parseResponse($bodyText));
        
        try {
            $sampleXml = '<foo>fff';
            $captureModel->parseResponse($sampleXml);
        } catch (Exception $e) {
            $this->assertEquals(
                Mage::helper('expercash/data')->__('Error while transforming response to simple xml.'),
                $e->getMessage()
          );
        }
        try {
            $sampleXml = '';
            $captureModel->parseResponse($sampleXml);
        } catch (Exception $e) {
            $this->assertEquals(Mage::helper('expercash/data')->__('Error while transforming response to simple xml.'),$e->getMessage());
        }
    }

    public function testGetClient()
    {
        $client = Mage::getModel('expercash/api_api')->getClient();
        $this->assertTrue($client instanceof Varien_Http_Client);
    }

    public function testSetConfig()
    {
        $model = Mage::getModel('expercash/api_api');
        $config = Mage::getModel('expercash/config');
        $model->setConfig($config);
        $this->assertTrue($model->getConfig() instanceof Expercash_Expercash_Model_Config);
    }
}

