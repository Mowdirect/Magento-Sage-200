<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

DROP TABLE IF EXISTS `{$this->getTable('sage_schedule')}`;

CREATE TABLE `{$this->getTable('sage_schedule')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `process_type` varchar(30) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `process_status` varchar(10) NOT NULL,
  `related_info` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

		
SQLTEXT;

$installer->run($sql);


$installer->endSetup();


$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'is_sage_exported', "tinyint(1) DEFAULT '0'");
$installer->getConnection()->addColumn($installer->getTable('sales/order_grid'), 'is_sage_exported', "tinyint(1) DEFAULT '0'");
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

$write->query("UPDATE ".$installer->getTable('sales/order')." SET is_sage_exported = 0;");
$write->query("UPDATE ".$installer->getTable('sales/order_grid')." SET is_sage_exported = 0;");
	 