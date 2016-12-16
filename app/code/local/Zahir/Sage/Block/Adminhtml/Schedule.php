<?php


class Zahir_Sage_Block_Adminhtml_Schedule extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_schedule";
	$this->_blockGroup = "sage";
	$this->_headerText = Mage::helper("sage")->__("Sage Schedule");
	$this->_addButtonLabel = Mage::helper("sage")->__("Add New Item");
	parent::__construct();
		$this->_removeButton('add');
	
	}

}