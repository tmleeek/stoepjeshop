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

class FME_Faqs_Model_Mysql4_Topic extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the faqs_id refers to the key field in your database table.
        $this->_init('faqs/topic', 'topic_id');
    }
	
	 protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('faqs_store'))
            ->where('topic_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
        
    }
	
	/**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
				
        $condition = $this->_getWriteAdapter()->quoteInto('topic_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('faqs_store'), $condition);
    
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['topic_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('faqs_store'), $storeArray);
        }
    
        return parent::_afterSave($object);
        
    }
	
	 /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param   string $identifier
     * @param   int $storeId
     * @return  int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'topic_id')
            ->join(
                array('store_table' => $this->getTable('faqs_store')),
                'main_table.topic_id = store_table.topic_id'
            )
            ->where('main_table.identifier = ?', $identifier)
            ->where('main_table.status = 1')
            ->where('store_table.store_id in (?) ', array(0, $storeId))
            ->order('store_table.store_id DESC');
            
        return $this->_getReadAdapter()->fetchOne($select);
    }
}