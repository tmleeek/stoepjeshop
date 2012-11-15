<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('dailydeal')};
CREATE TABLE {$this->getTable('dailydeal')} (
  `dailydeal_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(11) NOT NULL,
  `display_on` datetime NULL,
  `qty_sold` int(11) NOT NULL,
  `nr_views` int(11) NOT NULL,
  `disable` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`dailydeal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 