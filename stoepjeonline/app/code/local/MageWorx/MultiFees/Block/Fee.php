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

class MageWorx_MultiFees_Block_Fee extends Mage_Core_Block_Template
{
    private $_isEnabled;
    private $_fee    = null;
    private $_helper = null;

    protected function _construct()
    {
        $this->_helper    = Mage::helper('multifees');
        $this->_isEnabled = $this->_helper->isFeeEnabled();
        $this->_fee       = (double) $this->_getSession()->getMultifees();
    }

	protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadCalendarJs(true);
        }
        return parent::_prepareLayout();
    }

    public function getDateFormat()
    {
    	return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    public function getItems()
    {
        $productIds = Mage::getSingleton('checkout/cart')->getQuoteProductIds();
        if ($productIds) {
            $noFees = false;
            $productTypes = array();
            $productModel = Mage::getModel('catalog/product');
            $fees = array();
            $allAdditionalFees = array();
            $additionalFeesCount  = 0;
            foreach ($productIds as $productId) {
                $productModel = Mage::getModel('catalog/product');
                $product = $productModel->load($productId);
                $additionalFees = $product->getAdditionalFees();
                if ('-2' == $additionalFees) {
                    if (count ($productIds) == 1)
                    {
                        $noFees = true;
                    }
                    $additionalFees = '';
                    $additionalFeesCount++;
                }
                if (0 == count($additionalFees) || '-1' == $additionalFees) {
                    $productTypes[] = $product->getTypeId();
                } else {
                    $array = explode(',', $additionalFees);
                    $allAdditionalFees += $array;
                    $additionalFeesCount++;
                }
            }
        }

        $fees = $this->_getAdditionalFees($allAdditionalFees);

        //if (count($productIds) > $additionalFeesCount) {
        if (!$noFees)
        {
            $collection = Mage::getModel('multifees/fee')
                    ->getCollection()
                    ->addFeeStoreFilter()
                    ->addStatusFilter()
                    ->addCheckoutTypeFilter(true)
                    ->addSortOrderFilter()
                    ->addAdditionalFeesFilter($allAdditionalFees, true)
                    ->addProductTypeFilter($productTypes)
                    ->load();

            $this->_prepareTitle($collection);

            $allTypesCollection = Mage::getModel('multifees/fee')
                    ->getCollection()
                    ->addFeeStoreFilter()
                    ->addStatusFilter()
                    ->addCheckoutTypeFilter(true)
                    ->addSortOrderFilter()
                    ->addAdditionalFeesFilter($allAdditionalFees, true)
                    ->addAllProductTypesFilter()
                    ->load();

            $this->_prepareTitle($allTypesCollection);

            $items = $collection->getItems() + $allTypesCollection->getItems() + $fees;

            return $items;
        }
        else
            return null;
        //}
        //return $fees;
    }

    private function _getAdditionalFees($feeIds)
    {
        if (0 == count($feeIds)) {
            return array();
        }
        $collection = Mage::getModel('multifees/fee')
                ->getCollection()
                ->addFeeStoreFilter()
                ->addStatusFilter()
                ->addCheckoutTypeFilter(true)
                ->addSortOrderFilter()
                ->addAdditionalFeesFilter($feeIds, false)
                ->load();
        $this->_prepareTitle($collection);

        return $collection->getItems();
    }

    private function _prepareTitle(&$collection)
    {
    	$items = $collection->getItems();
    	if ($items) {
	    	foreach ($items as $item) {
	    		$feeTitle = Mage::helper('multifees')->getFeeTitle($item->getId());
	    		if ($feeTitle) {
	    			$item->setData('title', $feeTitle);
	    		}
	    	}
	    }
    }

    public function getOptionItems($feeId)
    {
		$collection = Mage::getResourceModel('multifees/option_collection')
			->addStoreFilter(Mage::app()->getStore()->getId())
        	->addFeeFilter($feeId)
    		->addPositionOrder(Varien_Data_Collection::SORT_ORDER_ASC)
    		->load();

		$this->_setCheckedFeeOption($collection, $feeId);

		return $collection->getItems();
    }

    private function _setCheckedFeeOption(&$collection, $feeId)
    {
    	$storeFees = $this->_getSession()->getStoreMultifees();
    	if ($storeFees) {
	    	$items = $collection->getItems();
	    	if ($items) {
	    		foreach ($items as $item) {
	    			if (isset($storeFees[$feeId]) && in_array($item->getId(), $storeFees[$feeId])) {
	    				$item->setIsDefault(1);
	    			} else {
	    				$item->setIsDefault();
	    			}
	    		}
		    }
    	}
    }

    public function getFeeMessage($feeId)
    {
    	$detailsFees = $this->_getSession()->getDetailsMultifees();
    	if (isset($detailsFees[$feeId]['message'])) {
    		return $this->htmlEscape($detailsFees[$feeId]['message']);
    	} else {
    		return '';
    	}
    }

    public function getFeeDate($feeId)
    {
    	$detailsFees = $this->_getSession()->getDetailsMultifees();
    	if (isset($detailsFees[$feeId]['date'])) {
    		return $detailsFees[$feeId]['date'];
    	} else {
    		return '';
    	}
    }

    public function getFeeHtml()
    {
        $html = Mage::getSingleton('cms/block')->load('cart-multi-fees')->getContent();
        return $html;
    }

    public function getOptionImgHtml($option, $groupId = null)
    {
    	return Mage::helper('multifees')->getOptionImgHtml($option, $groupId);
    }

    public function hasFee()
    {
        return !empty($this->_fee);
    }

    public function getStoreFee()
    {
        return Mage::app()->getStore()->convertPrice($this->_fee, true, true);
    }

    public function isCartEnabled()
    {
        return $this->_helper->isFeeEnabled();
    }

    protected function _toHtml()
    {
        if (!$this->_isEnabled) {
            return '';
        }
        return parent::_toHtml();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}