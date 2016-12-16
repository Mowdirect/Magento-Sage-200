<?php

class Zahir_Sage_Block_Adminhtml_Renderer_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        if (!$row->getData('is_sage_exported')) {
            return 'False';
        }

        return 'True';
    }
}