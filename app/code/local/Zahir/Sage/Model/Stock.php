<?php

class Zahir_Sage_Model_Stock extends Mage_Core_Model_Abstract
{
    var $_helper = null;
    var $_schedule= null;
    var $_inclprices = "true";
    var $_DbConnection = null;

    protected function _construct(){

       $this->_init("sage/schedule");
        $this->_helper=Mage::helper("sage");
        $this->_schedule=Mage::getModel("sage/schedule");
        $coreResource = Mage::getSingleton('core/resource');
        $this->_DbConnection = $coreResource->getConnection('core_write');

    }

    public function process ($xml=null) {
        if ($xml==null) return ;
        $time=time();
        $id="Stock-".$time;
        $scId=$this->_schedule->saveTransaction($id,"pending","StockUpdate");
        try {
            $count=$this->doImport($xml);
            $this->_schedule->updateTransaction($scId, $id,"Imported -".$count, "StockUpdate");
        } catch (Exception $e) {
            $this->_schedule->updateTransaction($scId, $id,"ERROR","StockUpdate");
            $this->_helper->logError("stock Process Error", $e->getMessage());
        }

    }
    private function doImport ($xml) {
        if ($xml==null) return 0;
        $itemcount=0;

        try {

            foreach ($xml->StockItem as $item) {
                $sku=$item->ItemCode;
                $qty=round($item->FreeStock,0);
                $result=$this->updateStock($sku,$qty);
                if ($result) {

                } else {
                    $this->addProduct($item);
                    $this->updateStock($sku,$qty);
                }
                $itemcount++;
            }
            return $itemcount;
        } catch (Exception $e) {

            $this->_helper->logError("Stock doImport Error", $e->getMessage());
            return $itemcount;
        }
    }
    private function updateStock($sku, $qty){
        try {

            $productId = $this->_helper->getIdFromSku($sku);
            if ($productId) {

                try {
                    /* $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                     $stockItem->setQty($qty);
                     $stockItem->save();
                     unset($stockItem);*/
                    $this->_DbConnection->query("UPDATE cataloginventory_stock_item s_i SET s_i.qty = '$qty', s_i.is_in_stock = IF('$qty'>0, 1,0) WHERE s_i.product_id = '$productId'");
                    return true;
                } catch (Exception $e) {
                    Mage::throwException('Update Error for: '. $sku.' Qty: '.$qty. ' Error:'. $e->getMessage());
                }

            } else {

                Mage::throwException('Cannot indetify Product from SKU: '. $sku);
            }
        } catch (Exception $e) {
            $this->_helper->logError("Stock updateStock Error Sku: ".$sku." Qty:".$qty, $e->getMessage());
            return false;
        }

    }

    private function addProduct($data) {

        try {
            $product = Mage::getModel("catalog/product");
            $product->setWebsiteIds(array(0))
                ->setAttributeSetId(9)
                ->setTypeId('simple')
                ->setStatus(2)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                ;
            $product->setData("sku", $data->ItemCode);
            $product->setData("name", $data->ItemName);
            $product->setData("description", $data->LongDescription);
            $product->setData("weight", $data->Weight);
            $product->setData("ean", $data->Barcode);
            $product->save();
        } catch (Exception $e) {
            $this->_helper->logError("Cannot add new product Sku: ".$data->ItemCode." Name:".$data->ItemName, $e->getMessage());
        }

    }

}
	 