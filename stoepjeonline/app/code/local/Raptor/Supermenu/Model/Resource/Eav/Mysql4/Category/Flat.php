<?php
/**
 * Raptor
 *
 * @category    Raptor
 * @package     Raptor_Supermenu
 * @copyright   Copyright (c) 2010 Raptor Commerce. (http://www.raptorcommerce.com)
 */


/**
 * We have rewritten the category resource model to include the new supermenu attributes (logo, columns etc) 
 */
class Raptor_Supermenu_Model_Resource_Eav_Mysql4_Category_Flat extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Flat
{

    /**
     * Load nodes by parent id
     *
     * @param integer $parentId
     * @param integer $recursionLevel
     * @param integer $storeId
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Flat
     */
    protected function _loadNodes($parentNode = null, $recursionLevel = 0, $storeId = 0)
    {
        $_conn = $this->_getReadAdapter();
        $startLevel = 1;
        $parentPath = '';
        if ($parentNode instanceof Mage_Catalog_Model_Category) {
            $parentPath = $parentNode->getPath();
            $startLevel = $parentNode->getLevel();
        } elseif (is_numeric($parentNode)) {
            $selectParent = $_conn->select()
                ->from($this->getMainStoreTable($storeId))
                ->where('entity_id = ?', $parentNode)
                ->where('store_id = ?', $storeId);
            if ($parentNode = $_conn->fetchRow($selectParent)) {
                $parentPath = $parentNode['path'];
                $startLevel = $parentNode['level'];
            }
        }
        // SUPERMENU CHANGES - Note how we have added the supermenu attributes to the from clause
        $select = $_conn->select()
            ->from(array('main_table'=>$this->getMainStoreTable($storeId)), array('main_table.entity_id', 'main_table.name', 'main_table.columns', 'main_table.column_width', 'main_table.logo', 'main_table.brand_columns', 'main_table.enable_brands', 'main_table.path', 'main_table.is_active', 'main_table.is_anchor'))
            ->joinLeft(
                array('url_rewrite'=>$this->getTable('core/url_rewrite')),
                'url_rewrite.category_id=main_table.entity_id AND url_rewrite.is_system=1 AND url_rewrite.product_id IS NULL AND url_rewrite.store_id="'.$storeId.'" AND url_rewrite.id_path LIKE "category/%"',
                array('request_path' => 'url_rewrite.request_path'))
            ->where('main_table.is_active = ?', '1')
//            ->order('main_table.path', 'ASC')
            ->order('main_table.position', 'ASC');
		// END SUPERMENU CHANGES


        if ($parentPath) {
            $select->where($_conn->quoteInto("main_table.path like ?", "$parentPath/%"));
        }
        if ($recursionLevel != 0) {
            $select->where("main_table.level <= ?", $startLevel + $recursionLevel);
        }

        $inactiveCategories = $this->getInactiveCategoryIds();

        if (!empty($inactiveCategories)) {
            $select->where('main_table.entity_id NOT IN (?)', $inactiveCategories);
        }

        $arrNodes = $_conn->fetchAll($select);
        $nodes = array();
        foreach ($arrNodes as $node) {
            $node['id'] = $node['entity_id'];
            $nodes[$node['id']] = Mage::getModel('catalog/category')->setData($node);
        }

        return $nodes;
    }
}
