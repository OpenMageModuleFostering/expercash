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

class Expercash_Expercash_Model_Resource_Directory_Region extends Mage_Directory_Model_Mysql4_Region
{

    /**
     * load region by name 
     *
     * @overrites \Mage_Directory_Model_Mysql4_Region::loadByName
     *
     * @param Mage_Directory_Model_Region $region
     * @param                             $regionName
     * @param                             $countryId
     *
     * @return $this
     */
    public function loadByName(Mage_Directory_Model_Region $region, $regionName, $countryId)
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();

        $select = $this->_read->select()
            ->from(array('region'=>$this->_regionTable))
            ->where('region.country_id=?', $countryId)
            ->where('region.default_name=?', $regionName)
            ->joinLeft(array('rname'=>$this->_regionNameTable),
                'rname.region_id=region.region_id AND rname.locale=\''.$locale.'\'',
                array('name'));


        $region->setData($this->_read->fetchRow($select));
        return $this;
    }
}