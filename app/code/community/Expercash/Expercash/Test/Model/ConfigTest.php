<?php

class Expercash_Expercash_Test_Model_ConfigTest extends EcomDev_PHPUnit_Test_Case_Config
{

    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $storeTwo;
    /**
     * @var Expercash_Expercash_Model_Config
     */
    protected $config;

    public function setUp()
    {
        parent::setUp();
        $this->config   = Mage::getModel('expercash/config');
        $this->store    = Mage::app()->getStore(0)->load(0);
        $this->storeTwo = Mage::app()->getStore(1)->load(1);

    }

    public function testGetEpiUrl()
    {
        $path = 'epi_url/url';

        $this->store->resetConfig();
        $this->store->setConfig($path, 'https://testgateway.com');
        $this->assertTrue(is_string($this->config->getEpiUrl($this->store->getId())));
        $this->assertEquals('https://testgateway.com', $this->config->getEpiUrl($this->store->getId()));
    }

    public function testGetConfigData()
    {
        $path = 'payment/' . 'expercashcc' . '/' . 'active';
        $this->store->resetConfig();
        $this->store->setConfig($path, true);
        $this->assertTrue((bool)$this->config->getConfigData('active', 'expercashcc', 0));

        $this->storeTwo->setConfig($path, false);
        $this->assertEquals(false, $this->config->getConfigData('active', 'expercashcc', 1));
    }

    public function testGetPopupId()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'popup_id';
        $this->store->resetConfig();
        $this->store->setConfig($path, '11');
        $this->assertEquals('11',$this->config->getPopupId(0));
        $this->assertNotEquals('1',$this->config->getPopupId(0));

        $this->storeTwo->setConfig($path, '15');
        $this->assertEquals('15', $this->config->getPopupId(1));
        $this->assertNotEquals('1',$this->config->getPopupId(0));
    }

    public function testGetProfilId()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'profil_id';
        $this->store->resetConfig();
        $this->store->setConfig($path, '11');
        $this->assertEquals('11',$this->config->getProfilId(0));
        $this->assertNotEquals('1',$this->config->getProfilId(0));

        $this->storeTwo->setConfig($path, '15');
        $this->assertEquals('15', $this->config->getProfilId(1));
        $this->assertNotEquals('1',$this->config->getProfilId(1));
    }

    public function testGetAuthorizationkey()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'authorization_key';
        $this->store->resetConfig();
        $this->store->setConfig($path, '11');
        $this->assertEquals('11',$this->config->getAuthorizationkey(0));
        $this->assertNotEquals('1',$this->config->getAuthorizationkey(0));

        $this->storeTwo->setConfig($path, '15');
        $this->assertEquals('15', $this->config->getAuthorizationkey(1));
        $this->assertNotEquals('1',$this->config->getAuthorizationkey(1));
    }

    public function testGetGatewayKey()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'gateway_key';
        $this->store->resetConfig();
        $this->store->setConfig($path, '11');
        $this->assertEquals('11',$this->config->getGatewayKey(0));
        $this->assertNotEquals('1',$this->config->getGatewayKey(0));

        $this->storeTwo->setConfig($path, '15');
        $this->assertEquals('15', $this->config->getGatewayKey(1));
        $this->assertNotEquals('1',$this->config->getGatewayKey(1));
    }

    public function testGetProjectId()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'project_id';
        $this->store->resetConfig();
        $this->store->setConfig($path, '11');
        $this->assertEquals('11',$this->config->getProjectId(0));
        $this->assertNotEquals('1',$this->config->getProjectId(0));

        $this->storeTwo->setConfig($path, '15');
        $this->assertEquals('15', $this->config->getProjectId(1));
        $this->assertNotEquals('1',$this->config->getProjectId(1));
    }

    public function testGetIframeCssClass()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'iframe_css_class';
        $this->store->resetConfig();
        $this->store->setConfig($path, 'example.css');
        $this->assertEquals('example.css',$this->config->getIframeCssClass(0));
        $this->assertNotEquals('1',$this->config->getIframeCssClass(0));

        $this->storeTwo->setConfig($path, 'example2.css');
        $this->assertEquals('example2.css', $this->config->getIframeCssClass(1));
        $this->assertNotEquals('1',$this->config->getIframeCssClass(1));
    }

    public function testGetIframeWidth()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'iframe_width';
        $this->store->resetConfig();
        $this->store->setConfig($path, '500');
        $this->assertEquals('500',$this->config->getIframeWidth(0));
        $this->assertNotEquals('1',$this->config->getIframeWidth(0));

        $this->storeTwo->setConfig($path, '300');
        $this->assertEquals('300', $this->config->getIframeWidth(1));
        $this->assertNotEquals('1',$this->config->getIframeWidth(1));
    }


    public function testGetIframeHeight()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'iframe_height';
        $this->store->resetConfig();
        $this->store->setConfig($path, '800');
        $this->assertEquals('800',$this->config->getIframeHeight(0));
        $this->assertNotEquals('1',$this->config->getIframeHeight(0));

        $this->storeTwo->setConfig($path, '200');
        $this->assertEquals('200', $this->config->getIframeHeight(1));
        $this->assertNotEquals('1',$this->config->getIframeHeight(1));
    }

    public function testGetCssUrl()
    {
        $path = Expercash_Expercash_Model_Config::PAYMENT_SERVICE_PATH.'css_url';
        $this->store->resetConfig();
        $this->store->setConfig($path, 'www.test.de');
        $this->assertEquals('www.test.de',$this->config->getCssUrl(0));
        $this->assertNotEquals('1',$this->config->getCssUrl(0));

        $this->storeTwo->setConfig($path, 'www.test2.de');
        $this->assertEquals('www.test2.de', $this->config->getCssUrl(1));
        $this->assertNotEquals('1',$this->config->getCssUrl(1));
    }

    public function testGetIframeTokenUrl()
    {
        $path = 'iframe_token_url/url';
        $url  = 'https://epi.expercash.net/iframe/post/v25/';
        $this->store->resetConfig();
        $this->store->setConfig($path,$url);
        $this->assertEquals($url,$this->config->getIframeTokenUrl());
        $this->assertNotEquals('https://www.google.de',$this->config->getIframeTokenUrl());
    }

    public function testGetIframeUrl()
    {
        $path = 'iframe_url/url';
        $url  = 'https://epi.expercash.net/epi_popup2.php';
        $this->store->resetConfig();
        $this->store->setConfig($path,$url);
        $this->assertEquals($url,$this->config->getIframeUrl());
        $this->assertNotEquals('https://www.google.de',$this->config->getIframeTokenUrl());
    }
}
