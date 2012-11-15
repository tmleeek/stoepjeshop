<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `promo_sku` TEXT
");

$this->endSetup();