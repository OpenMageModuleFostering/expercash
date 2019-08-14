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

class Expercash_Expercash_Model_Directory_Region 
{

    /**
     * check if directory modul version is above 1.6.0
     *
     * @return bool
     */
    public function isLegacyInstallation()
    {
        $directoryVersion = Mage::getConfig()->getModuleConfig('Mage_Directory')->version;
        return version_compare($directoryVersion, '1.6.0.0', '<=');
    }

    /**
     * load region by name
     *
     * @param $regionName
     * @param $countryId
     *
     * @return $this|Mage_Directory_Model_Region
     */
    public function loadByName($regionName, $countryId)
    {
        $region = Mage::getModel('directory/region');

        if ($this->isLegacyInstallation()) {
            Mage::getResourceModel('expercash/directory_region')->loadByName($region, $regionName, $countryId);
            return $region;
        }

        return $region->loadByName($regionName, $countryId);
    }
    
}