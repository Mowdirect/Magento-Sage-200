<?php
	
class Zahir_Sage_Block_Adminhtml_Schedule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "sage";
				$this->_controller = "adminhtml_schedule";
				$this->_updateButton("save", "label", Mage::helper("sage")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("sage")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("sage")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("schedule_data") && Mage::registry("schedule_data")->getId() ){

				    return Mage::helper("sage")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("schedule_data")->getId()));

				} 
				else{

				     return Mage::helper("sage")->__("Add Item");

				}
		}
}