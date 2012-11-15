<?php
/**
 * Scalena_News extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Scalena
 * @package    Scalena_News
 * @copyright  Copyright (c) 2009 Scalena Agency SA
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Scalena
 * @package    Scalena_News
 * @author     Anthony Charrex <anthony@scalena.com>
 */

class FME_Faqs_Block_Block extends Mage_Core_Block_Template
{
	 public function getItems($limit = 5)  {

		$collection = Mage::getModel('faqs/topic')->getCollection()
					->addStoreFilter(Mage::app()->getStore(true)->getId())
					->addOrder('main_table.created_time', 'desc')
					->setPageSize($limit)
					->getData();
								
      	return $collection;
        
    }  
}