<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExpercashControllerTest
 *
 * @author sebastian
 */
class Expercash_Expercash_Test_Controller_ExpercashControllerTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    protected $store;

    public function setUp()
    {
        $this->store = Mage::app()->getStore(0)->load(0);
        parent::setup();
    }


    /**
     * @loadFixture     orders
     * @loadExpectation orders
     */
    public function testCheckReturnedDataTrue()
    {

        if (Mage::helper('expercash/data')->isBelowCE17()) {
            $this->markTestSkipped('Magento Version not supported by Ecom Dev');
            return;
        }

        $helperMock = $this->getHelperMock(
            'expercash/payment', array('checkReturnedData')
        );
        $helperMock->expects($this->any())
            ->method('checkReturnedData')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'expercash/payment', $helperMock);
        $order = Mage::getModel('sales/order')->load(11);
        $this->assertEquals(0, $order->getEmailSent());
        Mage::app()->getRequest()->setParams($this->getResponseParams());

        $this->dispatch('expercash/expercash/notify', array('_store' => 'default'));


    }

    /**
     * @loadFixture     orders
     * @loadExpectation orders
     */
    public function testCheckReturnedDataNoRoute()
    {

        if (Mage::helper('expercash/data')->isBelowCE17()) {
            $this->markTestSkipped('Magento Version not supported by Ecom Dev');
            return;
        }

        $helperMock = $this->getHelperMock(
            'expercash/payment', array('checkReturnedData')
        );
        $helperMock->expects($this->any())
            ->method('checkReturnedData')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'expercash/payment', $helperMock);

        Mage::app()->getRequest()->setMethod('POST');
        Mage::app()->getRequest()->setParams($this->getResponseParams());
        $this->dispatch('expercash/expercash/notify');

    }



    /**
     * @loadFixture     orders.yaml
     */
    public function testBarzahlenPaid()
    {

        if (Mage::helper('expercash/data')->isBelowCE17()) {
            $this->markTestSkipped('Magento Version not supported by Ecom Dev');
            return;
        }

        $helperMock = $this->getHelperMock('expercash/payment', array('setExperCashData', 'getCheckSum'));
        $helperMock->expects($this->any())
            ->method('getCheckSum')
            ->will($this->returnValue('3c564b7cd1cd16ab30460e835163ead7'));
        $this->replaceByMock('helper', 'expercash/payment', $helperMock);

        Mage::app()->getRequest()->setParams($this->getBarzahlenPaidCallParams());
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load(12);
        $this->assertEquals(1, $order->getEmailSent());
        $this->dispatch('expercash/expercash/notify');
        $order = Mage::getModel('sales/order')->load(12);
        $this->assertEquals(1, $order->getEmailSent());
        $this->assertEquals(Expercash_Expercash_Model_Expercashpc::BARZAHLEN_STATUS_PAID, $order->getPayment()->getAdditionalInformation('paymentStatus'));


    }

    /**
     * @loadFixture              orders.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Response is not correct.
     */
    public function testBarzahlenSecondCallToOld()
    {
        if (Mage::helper('expercash/data')->isBelowCE17()) {
            $this->markTestSkipped('Magento Version not supported by Ecom Dev');
            return;
        }

        $helperMock = $this->getHelperMock('expercash/payment', array('setExperCashData', 'getCheckSum'));
        $helperMock->expects($this->any())
            ->method('getCheckSum')
            ->will($this->returnValue('3c564b7cd1cd16ab30460e835163ead7'));
        $this->replaceByMock('helper', 'expercash/payment', $helperMock);

        Mage::app()->getRequest()->setParams($this->getBarzahlenOpenParams());
        $this->dispatch('expercash/expercash/notify');
    }


    protected function getResponseParams()
    {
        return array(
            'transactionId' => 100000005,
            'amount'        => 3500,
            'currency'      => 'EUR',
            'paymentMethod' => 'foo',
            'GuTID'         => 'CC1724106675000',
            'exportKey'     => '972f10a9ef67f0583bc0c9e33709670d'
        );
    }

    protected function getBarzahlenPaidCallParams()
    {
        $params = array(
            'epi'           => '1',
            'transactionId' => '100000100',
            'amount'        => '3500',
            'currency'      => 'EUR',
            'paymentMethod' => 'BZ',
            'GuTID'         => 'BZ2006472637000',
            'exportKey'     => '3c564b7cd1cd16ab30460e835163ead7',
            'GuTID2'        => '316235217',
            'GuTID2Hash'    => 'b8bc23da725e115530a11bd62b429af2',
            'owner'         => 'SEBASTIAN ERTNER',
            'paymentStatus' => 'PAID'
        );
        return $params;
    }

    protected function getBarzahlenOpenParams()
    {
        $params = array(
            'epi'           => '1',
            'transactionId' => '100000101',
            'amount'        => '3500',
            'currency'      => 'EUR',
            'paymentMethod' => 'BZ',
            'GuTID'         => 'BZ2006472637000',
            'exportKey'     => '3c564b7cd1cd16ab30460e835163ead7',
            'GuTID2'        => '316235217',
            'GuTID2Hash'    => 'b8bc23da725e115530a11bd62b429af2',
            'owner'         => 'SEBASTIAN ERTNER',
            'paymentStatus' => 'PAID'
        );
        return $params;
    }

}
