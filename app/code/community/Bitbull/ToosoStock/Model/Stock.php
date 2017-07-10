<?php
/**
 * @category Bitbull
 * @package  Bitbull_ToosoStock
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_ToosoStock_Model_Stock
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

    public function syncronize(){
        //TODO: improve this
    }
}