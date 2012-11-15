<?php
/**
 * @category    Raptor
 * @package     Raptor_Supermenu
 * @copyright   Copyright (c) 2010 Raptor Commerce (http://www.raptorcommerce.com)
 */


/**
 * We have rewritten the category resource model to include the new supermenu attributes (logo, columns etc) 
 */
class Raptor_Supermenu_Model_Resource_Eav_Mysql4_Category_Tree extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
{

    /**
     * Enter description here...
     *
     * @param boolean $sorted
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
     */
    protected function _getDefaultCollection($sorted=false)
    {
        $this->_joinUrlRewriteIntoCollection = true;
        $collection = Mage::getModel('catalog/category')->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */

        $attributes = Mage::getConfig()->getNode('frontend/category/collection/attributes');
        if ($attributes) {
            $attributes = $attributes->asArray();
            $attributes = array_keys($attributes);
        }
        $collection->addAttributeToSelect($attributes);
        
		// SUPERMENU CHANGES
        $collection->addAttributeToSelect('columns');
		$collection->addAttributeToSelect('column_width');
		$collection->addAttributeToSelect('logo');
		$collection->addAttributeToSelect('brand_columns');
		$collection->addAttributeToSelect('enable_brands');
		// END SUPERMENU CHANGES

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addAttributeToSort($sorted);
            } else {
                $collection->addAttributeToSort('name');
            }
        }

        return $collection;
     }
}
