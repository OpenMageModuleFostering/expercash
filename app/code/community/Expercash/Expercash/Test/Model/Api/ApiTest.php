<?php


class Expercash_Expercash_Test_Model_Api_ApiTest extends EcomDev_PHPUnit_Test_Case_Config
{

    public function testGetIframeTokenSuccess()
    {
        $rawBody  = $this->getXml('SuccessFullResponse.xml');
        $clientMock = $this->getModelMock('expercash/api_api', array('_postRequest'));
        $clientMock->expects($this->once())
            ->method('_postRequest')
            ->will($this->returnValue($rawBody));

        $token = $clientMock->getIframeToken(array());
        $this->assertEquals('1234567', $token);

    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage invalid response from expercash
     */
    public function testGetIframeTokenFailedTransaction()
    {
        $rawBody  = $this->getXml('FailedTransaction.xml');
        $clientMock = $this->getModelMock('expercash/api_api', array('_postRequest'));
        $clientMock->expects($this->once())
            ->method('_postRequest')
            ->will($this->returnValue($rawBody));

        $clientMock->getIframeToken(array());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage invalid response from expercash
     */
    public function testGetIframeTokenResponseParamsMissing()
    {
        $rawBody  = $this->getXml('ResponseParamsMissing.xml');
        $clientMock = $this->getModelMock('expercash/api_api', array('_postRequest'));
        $clientMock->expects($this->once())
            ->method('_postRequest')
            ->will($this->returnValue($rawBody));

        $clientMock->getIframeToken(array());
    }



    protected function getXml($fileName)
    {
        $fileName
            = __DIR__ . DS . 'ApiTest' . DS . 'TestFiles' . DS . $fileName;
        if (!file_exists($fileName)) {
            throw new Exception($fileName . ' does not exist!');
        }
        if (!is_readable($fileName)) {
            throw new Exception($fileName . ' is not readable!');
        }
        return file_get_contents($fileName);
    }

}

