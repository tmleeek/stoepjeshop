<?php
/**
 * Faqs extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php

 * @category   FME
 * @package    Faqs
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Faqs_Model_Mysql4_Faqs_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('faqs/faqs');
    }
    
    public function addTopicFilter($topicId)
    {
		$this->getSelect()->join(
			array('topic_table' => $this->getTable('topic')),
			'main_table.topic_id = topic_table.topic_id',
			array()
		)
		->where('topic_table.topic_id = ?', $topicId);

		return $this;
    }
    
	public function addFaqFilter($id)
    {
        $this->getSelect()
            ->where('main_table.faqs_id = ?', $id);
        return $this;
    }
	
	
	
    public function addStatusFilter($status)
    {
        if (!Mage::app()->isSingleStoreMode()) {
			$this->getSelect()
			->where('main_table.status = ?', $status);
	
			return $this;
		}
		return $this;
    }
	
	public function getFaqData($faqs_id) {
		
		$this->setConnection($this->getResource()->getReadConnection());
		$this->getSelect()
			->from(array('main_table'=>$this->getTable('faqs')),'*')
			->where('faqs_id = ?', $faqs_id);
		return $this;
		
	}
}