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

class FME_Faqs_Model_Mysql4_Topic_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('faqs/topic');
    }
    
    public function addAttributeToFilter($attribute, $condition=null, $joinType='inner')
    {
        switch( $attribute ) {
            case 'status':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                return $this;
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
        }
        return $this;
    }
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()->join(
            array('store_table' => $this->getTable('faqs_store')),
            'main_table.topic_id = store_table.topic_id',
            array()
        )
        ->where('store_table.store_id in (?)', array(0, $store));
        return $this;
    }
}