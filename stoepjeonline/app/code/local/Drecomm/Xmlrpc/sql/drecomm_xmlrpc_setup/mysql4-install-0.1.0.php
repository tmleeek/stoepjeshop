<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('catalogrule')} ADD COLUMN `foreign_rule_id` VARCHAR(500) NOT NULL AFTER `rule_id`;
");