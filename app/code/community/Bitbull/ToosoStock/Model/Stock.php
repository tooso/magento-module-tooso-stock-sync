<?php
/**
 * @category Bitbull
 * @package  Bitbull_ToosoStock
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_ToosoStock_Model_Stock
{
    const DRY_RUN_FILENAME = 'tooso_stock_%store%.csv';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Client
     */
    protected $_client = null;

    /**
     * @var Bitbull_Tooso_Helper_Indexer
     */
    protected $_indexerHelper = null;

    public function __construct()
    {
        $this->_logger = Mage::helper('tooso/log');
        $this->_client = Mage::helper('tooso')->getClient();
        $this->_helper = Mage::helper('toosoStock');
        $this->_indexerHelper = Mage::helper('tooso/indexer');
    }

    /**
     * Synchronize Stock
     *
     * @return bool
     */
    public function synchronize(){
        try {
            $stores = $this->_helper->getStores();
            $this->_logger->debug("Stock Synchronization: using stores ".json_encode($stores));

            foreach ($stores as $storeCode => $storeId) {
                $storeLangCode = Mage::getStoreConfig('general/locale/code', $storeId);
                $this->_logger->debug("Stock Synchronization: synchronizing store ".$storeCode." [".$storeLangCode."]");

                $time_start = microtime(true);

                if($this->_helper->isDryRunEnabled()){
                    $this->_logger->debug("Stock Synchronization: store output into debug file ");
                    $this->_writeDebugFile($this->_getCsvContent($storeId), $storeCode);
                }else{
                    $client = Mage::helper('tooso')->getClient($storeCode, $storeLangCode);
                    $client->index($this->_getCsvContent($storeId), $this->_indexerHelper->getIndexerParams());
                }

                $time_end = microtime(true);
                $execution_time_s = ($time_end - $time_start);
                $execution_time_m = $execution_time_s/60;
                $execution_time_h = $execution_time_m/60;

                $execution_time = "";
                if($execution_time_h > 1){
                    $execution_time = round($execution_time_h, 3)."h";
                }else if($execution_time_m > 1){
                    $execution_time = round($execution_time_m, 3)."m";
                }else{
                    $execution_time = round($execution_time_s, 3)."s";
                }

                $this->_logger->debug("Stock Synchronization: store ".$storeCode." stock synchronized in ".$execution_time);
            }
        } catch (Exception $e) {
            $this->_logger->logException($e);
            return false;
        }

        return true;
    }

    /**
     * Get catalog exported CSV content
     *
     * return string
     */
    protected function _getCsvContent($storeId)
    {
        $headers = array(
            'sku' => 'sku',
            'qty' => 'qty'
        );

        $this->_logger->debug("Stock Synchronization: using attributes ".json_encode($headers));


        // load store products visible individually and select system attributes
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
        ;

        // load and select custom attributes
        $productCollection->joinTable('cataloginventory/stock_item',
            'product_id=entity_id',
            ['is_in_stock', 'qty'],
            '{{table}}.stock_id=1',
            'left'
        );

        // create new writer object
        $writer = $this->_getWriter();
        $writer->setHeaderCols($headers);

        $this->_logger->debug("Stock Synchronization: found ".$productCollection->getSize()." products");

        Mage::getSingleton('core/resource_iterator')->walk(
            $productCollection->getSelect(),
            array(
                array($this, 'productCollectionWalker')
            ),
            array(
                'storeId' => $storeId,
                'attributes' => $headers,
                'writer' => $writer
            )
        );

        return $writer->getContents();
    }

    /**
     * elaborate product collection row into CSV
     *
     * @param
     */
    public function productCollectionWalker($args){
        $product = Mage::getModel('catalog/product');
        $product->setData($args['row']);

        $storeId = $args['storeId'];
        $attributes = $args['attributes'];
        $writer = $args['writer'];

        $product->setStoreId($storeId);

        $row = array();
        $row["sku"] = $product->getSku();

        foreach ($attributes as $attributeCode) {
            $row[$attributeCode] = $product->getData($attributeCode);
        }
        $writer->writeRow($row);
    }

    /**
     * Print content into debug CSV file
     *
     * @param $content
     * @param $store_id
     * @return bool
     */
    protected function _writeDebugFile($content, $storeId = null){

        $logPath = Mage::getBaseDir('var').DS.'log';
        $fileName = "";
        if($storeId == null){
            $fileName = str_replace("_%store%", "", self::DRY_RUN_FILENAME);
        }else{
            $fileName = str_replace("%store%", $storeId, self::DRY_RUN_FILENAME);
        }
        $file_path = $logPath.DS.$fileName;
        $file = fopen($file_path, "w");
        if(!$file){
            $this->_logger->logException(new Exception("Unable to open file CSV debug file [".$file_path."]"));
            return false;
        }else{
            fwrite($file, $content);
            fclose($file);
            return true;
        }
    }

    /**
     * @return Mage_ImportExport_Model_Export_Adapter_Csv
     */
    protected function _getWriter()
    {
        return $this->_writer = Mage::getModel('importexport/export_adapter_csv', array());
    }
}