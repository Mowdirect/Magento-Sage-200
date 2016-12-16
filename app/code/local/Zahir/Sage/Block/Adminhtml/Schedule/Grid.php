<?php

class Zahir_Sage_Block_Adminhtml_Schedule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("scheduleGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("sage/schedule")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("sage")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("process_type", array(
				"header" => Mage::helper("sage")->__("Process Type"),
				"index" => "process_type",
				));
					$this->addColumn('created_at', array(
						'header'    => Mage::helper('sage')->__('Creation Date'),
						'index'     => 'created_at',
						'type'      => 'datetime',
					));
				$this->addColumn("process_status", array(
				"header" => Mage::helper("sage")->__("Status"),
				"index" => "process_status",
				));
				$this->addColumn("related_info", array(
				"header" => Mage::helper("sage")->__("Related Info"),
				"index" => "related_info",
					'renderer'=> new Zahir_Sage_Block_Adminhtml_Renderer_Order(),
				));
				$this->addColumn("updated_at", array(
				"header" => Mage::helper("sage")->__("Updated at"),
				"index" => "updated_at",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{	return;
		}


		
		protected function _prepareMassaction()
		{

			return $this;
		}
			

}