<?php
/**
 * @category Bitbull
 * @package  Bitbull_ToosoStock
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_ToosoStock_Helper_Stock extends Mage_Core_Helper_Abstract
{
    const XML_PATH_STOCK_SYNC_ACTIVE = 'toosostock/synchronization/active';
    const XML_PATH_STOCK_SYNC_STORES = 'toosostock/synchronization/stores_to_synchronize';
    const XML_PATH_STOCK_SYNC_DRY_RUN = 'toosostock/synchronization/dry_run_mode';
    const DRY_RUN_FILENAME = 'tooso_stock_%store%.csv';

    /**
     * Is Enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_STOCK_SYNC_ACTIVE);
    }

    /**
     * DryRun mode flag
     *
     * @return bool
     */
    public function isDryRunEnabled(){
        return Mage::getStoreConfigFlag(self::XML_PATH_STOCK_SYNC_DRY_RUN);
    }

    /**
     * Get stores grouped by lang code
     * @return array stores
     */
    public function getStores()
    {
        $storesConfig = Mage::getStoreConfig(self::XML_PATH_STOCK_SYNC_STORES);

        $stores = array();
        if($storesConfig == null || $storesConfig == "0"){
            $collection = Mage::getModel('core/store')->getCollection();
            foreach ($collection as $store) {
                $stores[$store->getCode()] = $store->getId();
            }
        }else{
            $storesArrayConfig = explode(",", $storesConfig);
            foreach ($storesArrayConfig as $storeId) {
                $store = Mage::getModel('core/store')->load($storeId);
                $stores[$store->getCode()] = $store->getId();
            }
        }

        $this->_logger->debug("Stock Synchronization: using stores ".json_encode($stores));

        return $stores;
    }


}