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

class MageWorx_MultiFees_Model_Mysql4_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public $_getStore = false;

    public function _construct()
    {
    	parent::_construct();
        $this->_init('multifees/option');
    }

    protected function _afterLoad()
    {
    	parent::_afterLoad();
    	$items = $this->getItems();
    	if ($items) {
    		$model = Mage::getResourceSingleton('multifees/language_option');
    		foreach ($items as $item) {
    			$result = array();
    			$item->setPrice(Mage::app()->getStore()->roundPrice($item->getPrice()));
    			$result = $model->getOption($item->getId());

    			if ($this->_getStore) {
					$storeOptions = $this->_prepareTitle($result);
					$result = isset($storeOptions[$this->_getStore]) ? $storeOptions[$this->_getStore] : $storeOptions[0];
    			}
				$options['option'] = $result;
    			$item->addData($options);
    		}
    	}
        return $this;
    }

    private function _prepareTitle($data)
    {
    	$result = array();
    	if ($data) {
    		foreach ($data as $value) {
    			$result[$value['store_id']] = Mage::helper('multifees')->htmlEscape($value['title']);
    		}
    	}
    	return $result;
    }

    public function addStoreFilter($type)
    {
    	$this->_getStore = $type;
    	return $this;
    }

    public function addFeeFilter($feeId)
    {
    	$this->getSelect()->where('main_table.mfo_fee_id = ?', $feeId);
        return $this;
    }

    public function addPositionOrder($term = null)
    {
    	$this->getSelect()->order('main_table.position', is_null($term) ? Varien_Data_Collection::SORT_ORDER_DESC : $term);
        return $this;
    }
}
