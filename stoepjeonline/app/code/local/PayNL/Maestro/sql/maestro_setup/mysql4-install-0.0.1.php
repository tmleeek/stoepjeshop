<?php
/**
 * @category    PayNL
 * @package     PayNL_Maestro
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

$installer = $this;
/* @var $installer PayNL_Maestro_Model_Mysql4_Setup */
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('maestro_api_debug')}`;
CREATE TABLE `{$this->getTable('maestro_api_debug')}` (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY  (`debug_id`),
  KEY `debug_at` (`debug_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

$installer->addAttribute('quote_payment', 'maestro_bank_id', array());
$installer->addAttribute('quote_payment', 'maestro_bank_list', array('type' => 'text'));
$installer->addAttribute('order_payment', 'maestro_bank_id', array());
$installer->addAttribute('order_payment', 'maestro_bank_title', array());
$installer->addAttribute('order', 'maestro_transaction_id', array());
$installer->addAttribute('order', 'maestro_paid_status', array('type' => 'int'));

?>