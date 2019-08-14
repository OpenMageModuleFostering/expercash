<?php
/**
 * Class Expercash_Expercash_Test_Model_ExpercashpcTest
 */
class Expercash_Expercash_Test_Model_ExpercashpcTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * @var Expercash_Expercash_Model_Config
     */
    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
    }


    public function testExpercashpcCodeProperty()
    {
        $expercashpc = Mage::getModel('expercash/expercashpc');
        $this->assertEquals('expercashpc', $expercashpc->getCode());
        $this->assertNotEquals('expercash', $expercashpc->getCode());
    }

    public function testExpercashpcCanCaptureProperty()
    {
        $expercashpc = Mage::getModel('expercash/expercashpc');
        $this->assertFalse($expercashpc->canCapture());
        $this->assertNotEquals(true, $expercashpc->canCapture());
    }

    public function testExpercashpcCanCapturePartialProperty()
    {
        $expercashpc = Mage::getModel('expercash/expercashpc');
        $this->assertEquals(false, $expercashpc->canCapturePartial());
        $this->assertNotEquals(true, $expercashpc->canCapturePartial());
    }

    public function testGetConfigData()
    {
        $this->assertEquals(
            Expercash_Expercash_Model_Expercashpc::PAYMENT_TYPE_PC,
            Mage::getModel('expercash/expercashpc')->getConfigData('paymenttype')
        );
        $this->assertNotEquals(
            Expercash_Expercash_Model_Expercashpc::PAYMENT_TYPE_PC,
            Mage::getModel('expercash/expercashpc')->getConfigData('iframeUrl')
        );
    }


    /**
     * @loadFixture quote.yaml
     */
    public function testIsAvailable()
    {
        $customerSession = $this->getModelMock('customer/session', array('init', 'save'));
        $this->replaceByMock('singleton', 'customer/session', $customerSession);


        // (1) valid quote but Barzahlen not active
        $quote = Mage::getModel('sales/quote')->load(1);
        self::app()->getStore()->setConfig('payment/expercashpc/active', 0);
        $this->assertFalse(Mage::getModel('expercash/expercashpc')->isAvailable($quote));

        // (2) invalid quote and Barzahlen active
        self::app()->getStore()->setConfig('payment/expercashpc/active', 1);
        $this->assertFalse(Mage::getModel('expercash/expercashpc')->isAvailable(null));

        // (2) valid quote and Barzahlen active
        $this->assertTrue(Mage::getModel('expercash/expercashpc')->isAvailable($quote));

        // (3) invalid quote and Barzahlen active
        $quote = Mage::getModel('sales/quote')->load(3);
        $this->assertFalse(Mage::getModel('expercash/expercashpc')->isAvailable($quote));

        // (4) invalid quote (currency) and Barzahlen active
        $quote = Mage::getModel('sales/quote')->load(4);
        $this->assertFalse(Mage::getModel('expercash/expercashpc')->isAvailable($quote));

        // (5) invalid quote (grand total) and Barzahlen active
        $quote = Mage::getModel('sales/quote')->load(5);
        $this->assertFalse(Mage::getModel('expercash/expercashpc')->isAvailable($quote));
    }
}
