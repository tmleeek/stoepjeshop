<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Advancedreports_Model_Mysql4_Order_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{     
    /**
     * Retrives helper
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }    
    
    /**
     * Before load action
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _beforeLoad()
    {                        
        parent::_beforeLoad();
                
        if ($this->_helper()->checkVersion('1.7.0.0')){            
            $wherePart = $this->getSelect()->getPart(Zend_Db_Select::SQL_WHERE);
            $this->getSelect()->reset(Zend_Db_Select::WHERE);
            
            $weHaveStoreId = false;
            foreach ($wherePart as $where){
                if (strpos($where, "store_id") !== false){                                        
                    if (!$weHaveStoreId){
                        if ($this->_helper()->getNeedMainTableAlias()){
                            $this->getSelect()->where(str_replace("AND ", "", str_replace("(store_id", "(main_table.store_id", $where) ));
                        } else {
                            $this->getSelect()->where(str_replace("AND ", "", str_replace("(store_id", "(e.store_id", $where) ));
                        }
                        
                        $weHaveStoreId = true;
                    }                       
                } else {
                    $this->getSelect()->where(str_replace("AND ", "", $where));
                }                                                                
            }                    
        }       
        return $this;
    }    
    
    
}