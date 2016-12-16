<?php

class Zahir_Sage_Model_Processor extends Mage_Core_Model_Abstract
{
    var $_helper = null;
    var $_schedule= null;
    var $_inclprices = "true";

    protected function _construct(){

       $this->_init("sage/schedule");
        $this->_helper=Mage::helper("sage");
        $this->_schedule=Mage::getModel("sage/schedule");

    }

    public function process ($orderid=null) {
        if ($orderid==null) return ;
        $scId=$this->_schedule->saveTransaction($orderid);
        try {
            if ($this->doExport($orderid)) {
                $this->_schedule->updateTransaction($scId, $orderid,"Exported");
            } else {
                Mage::throwException('Do Export failed');
            };

        } catch (Exception $e) {
            $this->_schedule->updateTransaction($scId, $orderid,"ERROR");
            $this->_helper->logError("saveTransaction Error", $e->getMessage());
        }

    }
    private function doExport ($orderid) {
        if ($orderid==null) return ;

        try {
            $order = Mage::getModel('sales/order')->load($orderid);

            if (!$order->getId()) { Mage::throwException('Order not found');}

            $xml=$this->buildXml($order);
            if (!$xml) { Mage::throwException('Xml Not created');}
            Mage::log($xml,null,"sage_integration_xml.log",true);

            if ($this->writeXml($order, $xml)) {
                return true;
            } else {
                return false;
            };



        } catch (Exception $e) {
            $this->_helper->logError("doExport Error", $e->getMessage());
            return false;
        }
    }

    private function buildXml($order) {
        $xml=null;

        $xml.='<?xml version="1.0" encoding="UTF-8"?>';
        $xml.='<Orders schemaVersion="4.0"><Order>';
        $xml.='<WebOrderReference>'.$order->getIncrementId().'</WebOrderReference>';
        $xml.='<OrderDate>'.date("Y-m-d",strtotime($order->getCreatedAt())).'</OrderDate>';
        $xml.='<OrderOnHold>'.$this->_helper->isHolded($order).'</OrderOnHold>';
        $xml.='<ArePricesTaxInclusive>'.$this->_inclprices.'</ArePricesTaxInclusive>';
        $xml.='<AccountReference>'.$this->_helper->getCustomerAccount($order).'</AccountReference>';
        $xml.='<CustomerOrderNumber>'.$order->getIncrementId().'</CustomerOrderNumber>';
        $xml.='<DeliveryAddress>'.$this->_helper->getDeliveryAddressBlock($order).'</DeliveryAddress>';
        $xml.='<CustomerAddress>'.$this->_helper->getCustomerAddressBlock($order).'</CustomerAddress>';
        $xml.='<Items>'.$this->_helper->getItemsBlock($order).'</Items>';
        $xml.='<ContactFirstName>'.htmlspecialchars($order->getCustomerFirstname(),ENT_XML1, 'UTF-8').'</ContactFirstName>';
        if ($middlename=$order->getCustomerMiddlename()) $xml.='<ContactMiddleName>'.$middlename.'</ContactMiddleName>';
        $xml.='<ContactLastName>'.htmlspecialchars($order->getCustomerLastname(),ENT_XML1, 'UTF-8').'</ContactLastName>';
        $xml.='<TelephoneNumber>'.$this->_helper->getCustomerPhone($order).'</TelephoneNumber>';
        $xml.='<EmailAddress>'.$order->getCustomerEmail().'</EmailAddress>';
        $xml.='<CarriageValue>'.$order->getShippingAmount().'</CarriageValue>';
        $xml.='<AnalysisCodes>'.$this->_helper->getOrderAnalysisBlock($order).'</AnalysisCodes>';
        $xml.='</Order></Orders>';
        /* Analysis codes*/
        return $xml;

    }

    private function writeXml($order, $xml) {
        try {
            $file = $this->_helper->getFileNameAndPath($order);
            @file_put_contents($file, $xml);
            return true;
        } catch (Exception $e) {
            $this->_helper->logError("writeXml Error: ".$file, $e->getMessage());
            return false;
        }
    }



}
	 