<?php

class Zahir_Sage_Block_Adminhtml_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        $order=false;
        if (is_numeric($value)) $order = Mage::getModel("sales/order")->load($value);
        if ($order):
            $url = Mage::helper('adminhtml')->getUrl('/sales_order/view', array('order_id' => $order->getId()));

            return '<a target="_blank" href="' . $url . '">' . $order->getIncrementId() . '</a>';
        else:
            return $value;
        endif;
    }
}
