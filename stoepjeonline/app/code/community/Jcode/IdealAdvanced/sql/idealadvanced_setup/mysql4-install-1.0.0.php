<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Advanced
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

$installer = $this;
/* @var $installer Mage_Ideal_Model_Mysql4_Setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('idealadvanced/api_debug')}`;
CREATE TABLE `{$this->getTable('idealadvanced/api_debug')}` (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY  (`debug_id`),
  KEY `debug_at` (`debug_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


/*
$setup->addAttribute('quote_payment', 'ideal_issuer_id', array());
$setup->addAttribute('quote_payment', 'ideal_issuer_list', array('type' => 'text'));
$setup->addAttribute('order_payment', 'ideal_issuer_id', array());
$setup->addAttribute('order_payment', 'ideal_issuer_title', array());
$setup->addAttribute('order_payment', 'ideal_transaction_checked', array('type'=>'int'));
*/
$installer->endSetup();