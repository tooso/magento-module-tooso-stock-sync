<?php
/**
 * @category Bitbull
 * @package  Bitbull_ToosoStock
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_ToosoStock_Model_Observer
{
    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;
    protected $_client = null;

    public function __construct()
    {
        $this->_logger = Mage::helper('tooso/log');
        $this->_client = Mage::helper('tooso')->getClient();
    }

    /**
     * Syncronize Stock
     *
     * @param  Mage_Cron_Model_Schedule $schedule
     * @return Bitbull_Tooso_Model_Observer
     */
    public function synchronizeStock(Mage_Cron_Model_Schedule $schedule)
    {
        if (Mage::helper('toosoStock')->isEnabled()) {
            $this->_logger->log('Start scheduled stock synchronization', Zend_Log::DEBUG);

            Mage::getModel('toosoStock/stock')->synchronize();

            $this->_logger->log('End scheduled stock synchronization', Zend_Log::DEBUG);
        }

        return $this;
    }

}
