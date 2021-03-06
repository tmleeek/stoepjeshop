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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */

class MageWorx_CustomOptions_Model_Observer {

    public function addOrderQty($observer) {
        if (!Mage::helper('customoptions')->isInventoryEnabled()) return $this;
        $orderItems = $observer->getEvent()->getOrder()->getItemsCollection();        
        foreach ($orderItems as $orderItem) {                        
            $poductOptions = $orderItem->getProductOptions();
            $qty = $orderItem->getQtyOrdered();
            foreach ($poductOptions['options'] as $option) {                
                switch ($option['option_type']) {
                    case 'drop_down':
                    case 'radio':
                    case 'checkbox':                        
                    case 'multiple':
                        $optionId = $option['option_id'];
                        $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();                                                
                        $optionTypeIds = explode(',', $option['option_value']);
                        foreach ($optionTypeIds as $optionTypeId) {                        
                            $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                            $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                            $sku = $productOptionValueModel->getSku();
                            if ($customoptionsQty!=='' || $sku!='') {
                                if (isset($poductOptions['info_buyRequest']['options_'.$optionId.'_qty'])) {
                                    $optionQty = intval($poductOptions['info_buyRequest']['options_'.$optionId.'_qty']);
                                } elseif (isset($poductOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty'])) {
                                    $optionQty = intval($poductOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty']);
                                } else {
                                    $optionQty = 1;
                                }                            
                                $optionTotalQty = ($customoptionsIsOnetime?$optionQty:$optionQty*$qty);
                                
                                if ($customoptionsQty!=='' && $customoptionsQty>0) {
                                    $customoptionsQty = $customoptionsQty - $optionTotalQty;
                                    if ($customoptionsQty<0) $customoptionsQty = 0;
                                    // model 'catalog/product_option_value' - do not use!
                                    Mage::getSingleton('core/resource')->getConnection('core_write')->update(strval(Mage::getConfig()->getTablePrefix()) . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);
                                }    
                                
                                if ($sku!=='') {
                                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                    if (isset($product) && $product && $product->getId() > 0) {
                                        $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);                                        
                                        if ($item->getQty() > 0) {
                                            if ($item->getQty() < $optionTotalQty) $optionTotalQty = intval($item->getQty());
                                            $item->subtractQty($optionTotalQty);
                                            $item->save();
                                        }                                        
                                    }
                                }    
                                
                            }    
                        }     
                }    
                    
            }
        }            
        return $this;
    }

    public function cancelOrderItem($observer) {        
        if (!Mage::helper('customoptions')->isInventoryEnabled()) return $this;
        //$qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();        
        $orderItem = $observer->getEvent()->getItem();                                  
        $poductOptions = $orderItem->getProductOptions();
        $qty = $orderItem->getQtyOrdered();
        foreach ($poductOptions['options'] as $option) {                
            switch ($option['option_type']) {
                case 'drop_down':
                case 'radio':
                case 'checkbox':                        
                case 'multiple':
                    $optionId = $option['option_id'];
                    $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();
                    $optionTypeIds = explode(',', $option['option_value']);
                    foreach ($optionTypeIds as $optionTypeId) {                    
                        $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                        $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                        $sku = $productOptionValueModel->getSku();
                        if ($customoptionsQty!=='' || $sku!='') {
                            if (isset($poductOptions['info_buyRequest']['options_'.$optionId.'_qty'])) {
                                $optionQty = intval($poductOptions['info_buyRequest']['options_'.$optionId.'_qty']);
                            } elseif (isset($poductOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty'])) {
                                $optionQty = intval($poductOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty']);                            
                            } else {
                                $optionQty = 1;
                            }                                                                        
                            $optionTotalQty = ($customoptionsIsOnetime?$optionQty:$optionQty*$qty);                        
                            
                            if ($customoptionsQty!=='') {
                                $customoptionsQty = $customoptionsQty + $optionTotalQty;                                
                                // model 'catalog/product_option_value' - do not use!
                                Mage::getSingleton('core/resource')->getConnection('core_write')->update(strval(Mage::getConfig()->getTablePrefix()) . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);                                
                            }
                            
                            if ($sku!=='') {
                                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                if (isset($product) && $product && $product->getId() > 0) {
                                    $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);                                    
                                    $item->addQty($optionTotalQty);
                                    $item->save();
                                }
                            }
                        }    
                    }    
            }            

        }        
        
        return $this;
        
    }

    public function creditMemoRefund($observer) {
        if (!Mage::helper('customoptions')->isInventoryEnabled()) return $this;
        
        $orderItems = $observer->getEvent()->getCreditmemo()->getOrder()->getItemsCollection();                
        foreach ($orderItems as $orderItem) {                        
            $poductOptions = $orderItem->getProductOptions();
            $qty = $orderItem->getQtyOrdered();
            foreach ($poductOptions['options'] as $option) {                
                switch ($option['option_type']) {
                    case 'drop_down':
                    case 'radio':
                    case 'checkbox':                        
                    case 'multiple':
                        $optionId = $option['option_id'];
                        $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();                        
                        $optionTypeIds = explode(',', $option['option_value']);
                        foreach ($optionTypeIds as $optionTypeId) {                        
                            $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                            $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                            $sku = $productOptionValueModel->getSku();
                            if ($customoptionsQty!=='' || $sku!='') {
                                if (isset($poductOptions['info_buyRequest']['options_'.$optionId.'_qty'])) {
                                    $optionQty = intval($poductOptions['info_buyRequest']['options_'.$optionId.'_qty']);
                                } elseif (isset($poductOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty'])) {
                                    $optionQty = intval($poductOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty']);    
                                } else {
                                    $optionQty = 1;
                                }                            
                                $optionTotalQty = ($customoptionsIsOnetime?$optionQty:$optionQty*$qty);
                                
                                if ($customoptionsQty!=='') {
                                    $customoptionsQty = $customoptionsQty + $optionTotalQty;
                                    // model 'catalog/product_option_value' - do not use!
                                    Mage::getSingleton('core/resource')->getConnection('core_write')->update(strval(Mage::getConfig()->getTablePrefix()) . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);
                                }
                                
                                if ($sku!=='') {
                                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                    if (isset($product) && $product && $product->getId() > 0) {
                                        $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);                                    
                                        $item->addQty($optionTotalQty);
                                        $item->save();
                                    }
                                }                                
                            }    
                        }     
                }    
                    
            }
        }            
        return $this;                                              
    }

    /*public function createOrderItem($observer) {        
        $item = $observer->getEvent()->getItem();

        $children = $item->getChildrenItems();

        if ($item->getId() && empty($children)) {
            $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

            $optionsIdsIterator = Mage::getModel('sales/quote_item_option')
                    ->getCollection()
                    ->addFieldToFilter('item_id', $item->getQuoteItemId())
                    ->addFieldToFilter('code', 'option_ids')
                    ->getIterator();
            $optionIds = current($optionsIdsIterator);
            if ($optionIds) {
                $optionIds = $optionIds->getValue();
                $optionIds = explode(',', $optionIds);

                $quoteItem = Mage::getModel('sales/quote_item')
                        ->load($item->getQuoteItemId());

                foreach ($optionIds as $optionId) {
                    $typeIdIterator = Mage::getModel('sales/quote_item_option')
                            ->getCollection()
                            ->addFieldToFilter('item_id', $item->getQuoteItemId())
                            ->addFieldToFilter('code', 'option_' . $optionId)
                            ->getIterator();
                    $typeId = current($typeIdIterator);
                    $typeId = $typeId->getValue();

                    $typeValue = Mage::getModel('catalog/product_option_value')
                            ->load($typeId);
                    if ($typeValue->getCustomoptionsQty() != '' && is_numeric($typeValue->getCustomoptionsQty()) && $typeValue->getCustomoptionsQty() > 0) {
                        $qty = $typeValue->getCustomoptionsQty() - intval($quoteItem->getQty());
                        if ($qty < 0)
                            $qty = 0;
                        $title = Mage::getResourceSingleton('customoptions/product_option')
                                ->getTitle($typeId, 0);
                        
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $typeValue->getSku());
                        if (isset($product) && $product && $product->getId() > 0) {
                            $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            if ($item->checkQty($qty)) {
                                $item->subtractQty($quoteItem->getQty());
                                $item->save();
                            }
                        }

                        $typeValue->setCustomoptionsQty($qty);
                        $typeValue->save();
                        Mage::getResourceSingleton('customoptions/product_option')
                                ->setTitle($typeId, 0, $title);
                    }
                }
            }
        }

        return $this;
    }*/  
    

}