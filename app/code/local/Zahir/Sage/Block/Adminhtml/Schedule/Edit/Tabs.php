<?php
class Zahir_Sage_Block_Adminhtml_Schedule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("schedule_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("sage")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("sage")->__("Item Information"),
				"title" => Mage::helper("sage")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("sage/adminhtml_schedule_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
