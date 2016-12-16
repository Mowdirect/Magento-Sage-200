<?php

class Zahir_Sage_Model_Schedule extends Mage_Core_Model_Abstract
{
    var $_helper = null;

    protected function _construct(){

       $this->_init("sage/schedule");
        $this->_helper=Mage::helper("sage");

    }

    public function saveTransaction ($related_info=null, $process_status="pending", $process_type="order") {
        try {
            $this->setProcessType($process_type);
            $this->setProcessStatus($process_status);
            $this->setRelatedInfo($related_info);
            $this->save();
            return $this->getId();
        } catch (Exception $e) {
            $this->_helper->logError("saveTransaction Error", $e->getMessage());
        }

    }

    public function updateTransaction ($id=null,$related_info=null, $process_status="pending", $process_type="order") {
        try {
            $this->load($id);

            $this->setProcessType($process_type);
            $this->setProcessStatus($process_status);
            $this->setRelatedInfo($related_info);
            $this->save();
            if (($process_type=="order") && ($process_status=="ERROR")) {
                $order=Mage::getModel("sales/order")->load($related_info)->setIsSageExported(2)->save();
            }
            if (($process_type=="order") && ($process_status=="Exported")) {
                $order=Mage::getModel("sales/order")->load($related_info)->setIsSageExported(1)->save();
            }
        } catch (Exception $e) {
            $this->_helper->logError("updateTransaction Error", $e->getMessage());
        }

    }

}
	 