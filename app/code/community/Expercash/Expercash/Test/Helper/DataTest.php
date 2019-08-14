<?php

class Expercash_Expercash_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    
    /**
     * Test for getAdditionalPaymentParams
     */
    public function testGetAdditionalPaymentParams()
    {
        $paymentParams = array(
            'owner',
            'maskedPan',
            'cardScheme',
            'validThru',
            'panCountry',
            'ipCountry',
            'attemptsTotal',
            'attemptsSameAction',
            '3dsStatus',
            'paymentStatus'
        );
        $this->assertEquals($paymentParams, Mage::helper('expercash/data')->getAdditionalPaymentParams());
    }
}