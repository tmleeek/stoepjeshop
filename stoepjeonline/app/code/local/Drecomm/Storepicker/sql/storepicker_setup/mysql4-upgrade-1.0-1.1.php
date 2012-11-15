<?php
$installer = $this;

$installer->startSetup();
try{
    $installer->run("
ALTER TABLE {$this->getTable('storepicker/stores')}
ADD COLUMN `customerno` VARCHAR(50) NOT NULL AFTER `id`,
ADD INDEX `customerno` (`customerno`);
");
}catch(Exception $e){}

$installer->endSetup();
