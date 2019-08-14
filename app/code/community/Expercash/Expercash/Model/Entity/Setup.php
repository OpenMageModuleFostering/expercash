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
class Expercash_Expercash_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{

    public function getDefaultEntities()
    {
        return array(
            'order_payment' => array(
                'entity_model' => 'sales/order_payment',
                'table' => 'sales/order_entity',
                'attributes' => array(
                    'parent_id' => array(
                        'type' => 'static',
                        'backend' =>'sales_entity/order_attribute_backend_child'
                    ),
                    'expercash_gutid' => array('type' => 'varchar'),
                    'expercash_transaction_id' => array('type' => 'varchar'),
                    'expercash_gutid_capture' => array('type' => 'varchar'),
                    'expercash_paymenttype' => array('type' => 'varchar'),
                    'expercash_epi_payment_id' => array('type' => 'varchar'),
                    'expercash_request_type' => array('type' => 'varchar'),
                ),
            ),
        );
    }
}
