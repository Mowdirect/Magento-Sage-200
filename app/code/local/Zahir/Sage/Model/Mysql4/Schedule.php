<?php
class Zahir_Sage_Model_Mysql4_Schedule extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("sage/schedule", "id");
    }
}