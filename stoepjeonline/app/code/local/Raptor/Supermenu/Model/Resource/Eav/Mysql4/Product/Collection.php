<?php
/**
 * Raptor
 *
 * @category    Raptor
 * @package     Raptor_Supermenu
 * @copyright   Copyright (c) 2010 Raptor Commerce. (http://www.raptorcommerce.com)
 *
 * We have rewritten this class to allow us to filter by multiple category ids instead
 * of the default which is a single category id
 */
class Raptor_Supermenu_Model_Resource_Eav_Mysql4_Product_Collection
extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{

	/**
	 * IMPORTANT - Flat products only return system attributes i.e. no brand!!
	 */
	public function isEnabledFlat() {
		return false;
	}

	public function addCategoriesFilter($categoryIds)
	{
		$this->_productLimitationFilters['category_id'] = $categoryIds;
		($this->getStoreId() == 0)? $this->_applyZeroStoreProductLimitations() : $this->_applyProductLimitations();

		return $this;
	}
	 

	/**
	 * Apply limitation filters to collection
	 *
	 * Method allows using one time category product index table (or product website table)
	 * for different combinations of store_id/category_id/visibility filter states
	 *
	 * Method supports multiple changes in one collection object for this parameters
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	protected function _applyProductLimitations()
	{
		$this->_prepareProductLimitationFilters();
		$this->_productLimitationJoinWebsite();
		$this->_productLimitationJoinPrice();
		$filters = $this->_productLimitationFilters;

		if (!isset($filters['category_id']) && !isset($filters['visibility'])) {
			return $this;
		}

		$conditions = array(
            'cat_index.product_id=e.entity_id',
		$this->getConnection()->quoteInto('cat_index.store_id=?', $filters['store_id'])
		);
		if (isset($filters['visibility']) && !isset($filters['store_table'])) {
			$conditions[] = $this->getConnection()
			->quoteInto('cat_index.visibility IN(?)', $filters['visibility']);
		}
		// SUPERMENU CHANGE - Note how we use an in clause if we have an array
		// of category ids
		if (is_array($filters['category_id'])) {
			$inCondition = implode(',', $filters['category_id']);
			$conditions[] = $this->getConnection()
			->quoteInto('cat_index.category_id in (?)', $inCondition);
		} else {
			$conditions[] = $this->getConnection()
			->quoteInto('cat_index.category_id=?', $filters['category_id']);
		}
		// END SUPERMENU CHANGE
		if (isset($filters['category_is_anchor'])) {
			$conditions[] = $this->getConnection()
			->quoteInto('cat_index.is_parent=?', $filters['category_is_anchor']);
		}

		$joinCond = join(' AND ', $conditions);
		$fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
		if (isset($fromPart['cat_index'])) {
			$fromPart['cat_index']['joinCondition'] = $joinCond;
			$this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
		}
		else {
			$this->getSelect()->join(
			array('cat_index' => $this->getTable('catalog/category_product_index')),
			$joinCond,
			array('cat_index_position' => 'position')
			);
		}

		$this->_productLimitationJoinStore();

		Mage::dispatchEvent('catalog_product_collection_apply_limitations_after', array(
            'collection'    => $this
		));

		return $this;
	}
}
