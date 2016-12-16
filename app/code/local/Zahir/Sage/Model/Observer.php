<?php

class Zahir_Sage_Model_Observer
{
    public function addColumn($observer)
    {
        try {
            $event = $observer->getEvent();
            if ($event->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
                $this->_grid = $event->getBlock();
                $this->_collection = $this->_grid->getCollection();

                $this->_grid->addColumnAfter(
                    'is_sage_exported',
                    array (
                        'header'   => Mage::helper ('sage')->__('Sage Export'),
                        'width'    => '80px',
                        'index'    => 'is_sage_exported',
                        'type'     => 'options',
                        'options'  => array(1 => 'True', 0 => 'False'),
                        'renderer' => new Zahir_Sage_Block_Adminhtml_Renderer_Renderer(),
                        'align'    => 'center'
                    ),
                    'status'
                );

                $this->_grid->sortColumnsByOrder();

            }
        } catch (Exception $e) {

        }
    }

}