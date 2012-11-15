<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_MultiFees_Model_Mysql4_Fee_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_previewFlag;

    public function _construct()
    {
    	parent::_construct();
        $this->_init('multifees/fee');
    }

    protected function _initSelect()
    {
    	parent::_initSelect();
    	$this->getSelect()->join(
            array('language_fee' => $this->getTable('multifees/language_fee')),
	    	'main_table.fee_id = language_fee.mfl_fee_id',
	    	array('title')
        )
        ->where('language_fee.store_id = ?', Mage::app()->getStore()->getId());
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            $this->getSelect()->join(
                array('store_table' => $this->getTable('multifees/store')),
                'main_table.fee_id = store_table.mfs_fee_id',
                array()
            )
            ->where('store_table.store_id IN (?)', ($withAdmin ? array(0, $store) : $store))
            ->group('main_table.fee_id');

            $this->setFlag('store_filter_added', true);
        }
        return $this;
    }

	public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

	protected function _afterLoad()
    {
        if ($this->_previewFlag) {
            $items = $this->getColumnValues('fee_id');
            if (count($items)) {
                $select = $this->getConnection()->select()
                        ->from($this->getTable('multifees/store'), array('mfs_fee_id', 'store_id'))
                        ->where($this->getTable('multifees/store').'.mfs_fee_id IN (?)', $items);

                $result = $this->getConnection()->fetchPairs($select);
                if ($result) {
                    foreach ($this as $item) {
                        if (!isset($result[$item->getData('fee_id')])) {
                            continue;
                        }
                        if ($result[$item->getData('fee_id')] == 0) {
                            $stores = Mage::app()->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        } else {
                            $storeId = $result[$item->getData('fee_id')];
                            $storeCode = Mage::app()->getStore($storeId)->getCode();
						}
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                    }
                }
            }
        }
        parent::_afterLoad();
    }

    public function addFeeStoreFilter($store = null, $withAdmin = true)
    {
    	if (is_null($store)) {
    		$store = Mage::app()->getStore()->getId();
    	}
    	$this->getSelect()->reset('where');
		$this->getSelect()->join(
                array('store_table' => $this->getTable('multifees/store')),
                'main_table.fee_id = store_table.mfs_fee_id',
                array()
            )
            ->where('store_table.store_id IN (?)', ($withAdmin ? array(0, $store) : $store))
            ->group('main_table.fee_id');

		return $this;
    }

    public function addStatusFilter()
    {
		$this->getSelect()->where('main_table.status = ?', MageWorx_MultiFees_Helper_Data::STATUS_VISIBLE);
        return $this;
    }

    public function addSortOrderFilter()
    {
		$this->getSelect()->order('main_table.sort_order', Varien_Data_Collection::SORT_ORDER_ASC);
        return $this;
    }

    public function addCheckoutTypeFilter($isNo = false)
    {
    	$sin = ($isNo === true) ? '!=' : '=';
        $this->getSelect()->where("main_table.checkout_type {$sin} ?", MageWorx_MultiFees_Helper_Data::IS_CHECKOUT_TYPE);
        return $this;
    }

    public function addAllProductTypesFilter()
    {
        $this->getSelect()->where('main_table.apply_to = \'\'');
        return $this;
    }

    public function addAdditionalFeesFilter($feeIds, $isNo = true) {
        if (0 == count($feeIds)) {
            return $this;
        }
        $sin = ($isNo === true) ? 'NOT' : '';
        $this->getSelect()->where("main_table.fee_id {$sin} IN(?)", $feeIds);
        return $this;
    }

    public function addProductTypeFilter($productTypes) {
        if (0 == count($productTypes)) {
            $productTypes[] = 'nothing';
        }
        $query = '';
        foreach ($productTypes as $productType) {
            $query .= '(main_table.apply_to LIKE \'%' . $productType . '%\') OR ';
        }
        $query = substr($query, 0, strlen($query) - 4);
        $this->getSelect()->where($query);
        return $this;
    }

    public function addCheckoutPaymentTypeFilter()
    {
        $this->getSelect()->where('main_table.input_type = ?', MageWorx_MultiFees_Helper_Data::CHECKOUT_TYPE_PAYMENT);
        return $this;
    }

    public function addCheckoutShippingTypeFilter()
    {
        $this->getSelect()->where('main_table.input_type = ?', MageWorx_MultiFees_Helper_Data::CHECKOUT_TYPE_SHIPPING);
        return $this;
    }
}