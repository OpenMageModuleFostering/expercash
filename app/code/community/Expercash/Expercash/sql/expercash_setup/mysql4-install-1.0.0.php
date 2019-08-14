<?php
/**
 * Expercash Expercash
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category    Expercash
 * @package     Expercash_Expercash
 * @copyright   Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();
$installer->installEntities();

if (Mage::getVersion() >= 1.1) {
    $installer->startSetup();
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_payment'), 
        'expercash_gutid', 'VARCHAR(255) NOT NULL'
    );
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_payment'), 
        'expercash_transaction_id', 'VARCHAR(255) NOT NULL'
    );
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_payment'), 
        'expercash_gutid_capture', 'VARCHAR(255) NOT NULL'
    );
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_payment'), 
        'expercash_paymenttype', 'VARCHAR(255) NOT NULL'
    );
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_payment'), 
        'expercash_epi_payment_id', 'VARCHAR(255) NOT NULL'
    );
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_payment'), 
        'expercash_request_type', 'VARCHAR(255) NOT NULL'
    );
    $installer->endSetup();
}

$installer->endSetup();