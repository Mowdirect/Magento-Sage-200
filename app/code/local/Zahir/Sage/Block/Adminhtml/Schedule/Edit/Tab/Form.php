<?php
class Zahir_Sage_Block_Adminhtml_Schedule_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("sage_form", array("legend"=>Mage::helper("sage")->__("Item information")));

				
						$fieldset->addField("process_type", "text", array(
						"label" => Mage::helper("sage")->__("Process Type"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "process_type",
						));
					
						$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
							Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
						);

						$fieldset->addField('created_at', 'date', array(
						'label'        => Mage::helper('sage')->__('Creation Date'),
						'name'         => 'created_at',					
						"class" => "required-entry",
						"required" => true,
						'time' => true,
						'image'        => $this->getSkinUrl('images/grid-cal.gif'),
						'format'       => $dateFormatIso
						));
						$fieldset->addField("process_status", "text", array(
						"label" => Mage::helper("sage")->__("Status"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "process_status",
						));
					
						$fieldset->addField("related_info", "text", array(
						"label" => Mage::helper("sage")->__("Related Info"),
						"name" => "related_info",
						));
					
						$fieldset->addField("updated_at", "text", array(
						"label" => Mage::helper("sage")->__("Updated at"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "updated_at",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getScheduleData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getScheduleData());
					Mage::getSingleton("adminhtml/session")->setScheduleData(null);
				} 
				elseif(Mage::registry("schedule_data")) {
				    $form->setValues(Mage::registry("schedule_data")->getData());
				}
				return parent::_prepareForm();
		}
}
