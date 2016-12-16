<?php

class Zahir_Sage_Adminhtml_ScheduleController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("sage/schedule")->_addBreadcrumb(Mage::helper("adminhtml")->__("Schedule  Manager"),Mage::helper("adminhtml")->__("Schedule Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Sage"));
			    $this->_title($this->__("Sage Schedule"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{
		}

		public function newAction()
		{
		}

		public function saveAction()
		{
		}

		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("sage/schedule");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("sage/schedule");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'schedule.csv';
			$grid       = $this->getLayout()->createBlock('sage/adminhtml_schedule_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		}
}
