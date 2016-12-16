<?php

class Zahir_Sage_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    public function getRowClass($order)
    {
        if ($order->getIsSageExported()==1) {
            return 'sage-processed-row';
        } else if($order->getIsSageExported()==0){
            return 'sage-pending-row';
        } else if ($order->getIsSageExported()==2) {
            return 'sage-error-row';
        }
    }
}