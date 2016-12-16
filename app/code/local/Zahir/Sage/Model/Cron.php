<?php

class Zahir_Sage_Model_Cron extends Mage_Core_Model_Abstract
{
    var $_helper = null;
    var $_stockfilename="StockItems";

    protected function _construct(){

        $this->_init("sage/schedule");
        $this->_helper=Mage::helper("sage");

    }

    public function runExport () {
        if ($this->_helper->getGeneralConfig("status")) {
            $orderstatus=$this->_helper->getGeneralConfig("order_status");
            $ostatuses=explode(",",$orderstatus);
            $processor=Mage::getModel("sage/processor");
            /* This will collect all orders in the given state, from the last 48 hours */
            $time = date("Y-m-d H:i:s", time() - 3600 * 24 * 2);

            foreach($ostatuses as $status){
                try {
                    $orders= $this->_helper->getOrders($status, $time);
                    foreach ($orders as $order) {
                        $processor->process($order->getId());
                    }
                } catch (Exception $e) {
                    $this->_helper->logError("CRON ORDER Error Status:".$status, $e->getMessage() );
                }
            }
        }
    }

    public function runImport () {

        if ($this->_helper->getStockConfig("status")) {
            $path=$this->_helper->getStockConfig("path");
            $filename=$this->_stockfilename.".xml";
            $processor=Mage::getModel("sage/stock");
            $files=glob($path.$filename);
            if ($files) {
                foreach ($files as $file) {
                    $newname=$file.".ims";
                    $rename=rename($file,$newname);
                    $xml=simplexml_load_file($newname);
                    $processor->process($xml);
                    try {
                    if (file_exists($newname)) {
                        unlink($newname);
                    }} catch (Exception $e) {
                        $this->_helper->logError("CRON STOCK Error","Cannot delete file;" );
                    }
                }
                $this->_helper->stockReindex();
            } else {
                $this->_helper->logError("CRON STOCK Error","No stock file there;" );
            }
        }
    }

}
	 