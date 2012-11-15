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
class MageWorx_CustomOptions_Model_Catalog_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price {

    /**
     * Apply options price
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @param double $finalPrice
     * @return double
     */         
     
    protected function _applyOptionsPrice($product, $qty, $finalPrice) {
        if ($optionIds = $product->getCustomOption('option_ids')) {
            $basePrice = $finalPrice;
            $store = $product->getStore();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {                    
                    $optionQty = null;
                    switch ($option->getType()) {
                        case 'checkbox':                            
                            $quoteItemOptionInfoBuyRequest = unserialize($product->getCustomOption('info_buyRequest')->getValue());
                            if (isset($quoteItemOptionInfoBuyRequest['options'][$optionId])) {                                                                
                                $optionValues = array();
                                $optionQtyArr = array();
                                foreach ($option->getValues() as $key=>$itemV) {                                    
                                    if (isset($quoteItemOptionInfoBuyRequest['options_'.$optionId.'_'.$itemV->getOptionTypeId().'_qty'])) $optionQty = intval($quoteItemOptionInfoBuyRequest['options_'.$optionId.'_'.$itemV->getOptionTypeId().'_qty']); else $optionQty = 1;
                                    $optionQtyArr[$itemV->getOptionTypeId()] = $optionQty;
                                    if (!$itemV->getUpdateTitleFlag()) {
                                    	$optionTotalQty = ($option->getCustomoptionsIsOnetime()?$optionQty:$optionQty*$qty);
                                    	$price = (($itemV->getPriceType()=='percent')?$basePrice * $itemV->getPrice() / 100 : $itemV->getPrice());
                                    	$itemV->setTitle(($optionTotalQty>1?$optionTotalQty.' x ':'') . $itemV->getTitle() . ($price>0?' - '.Mage::helper('customoptions')->currencyByStore($price*$optionTotalQty, $store, true, false):''));
                                    	$itemV->setUpdateTitleFlag(1);
                                    }	
                                    $optionValues[$key]=$itemV;
                                }
                                $optionQty = $optionQtyArr;
                                $option->setValues($optionValues);
                                break;                                
                            }
                            break;
                        case 'drop_down':
                        case 'radio':                            
                            $quoteItemOptionInfoBuyRequest = unserialize($product->getCustomOption('info_buyRequest')->getValue());
                            if (isset($quoteItemOptionInfoBuyRequest['options_'.$optionId.'_qty'])) $optionQty = intval($quoteItemOptionInfoBuyRequest['options_'.$optionId.'_qty']); else $optionQty = 1;
                        case 'multiple':
                            if (!isset($optionQty)) $optionQty = 1;
                            $optionValues = array();                        
                            foreach ($option->getValues() as $key=>$itemV) {                                
                                $optionTotalQty = ($option->getCustomoptionsIsOnetime()?$optionQty:$optionQty*$qty);                                
                                if (!$itemV->getUpdateTitleFlag()) {
                                	$price = (($itemV->getPriceType()=='percent')?$basePrice * $itemV->getPrice() / 100 : $itemV->getPrice());                                
                                	$itemV->setTitle(($optionTotalQty>1?$optionTotalQty.' x ':'') . $itemV->getTitle() . ($price>0?' - '.Mage::helper('customoptions')->currencyByStore($price*$optionTotalQty, $store, true, false):''));
                                	$itemV->setUpdateTitleFlag(1);
                                }
                                $optionValues[$key]=$itemV;
                            }
                            $option->setValues($optionValues);
                            break;
                        case 'field':
                        case 'area':
                        case 'file':
                        case 'date':
                        case 'date_time':
                        case 'time':
                            $optionTotalQty = ($option->getCustomoptionsIsOnetime()?1:$qty);
                            if (!$option->getUpdateTitleFlag()) {
                            	$price = (($option->getPriceType()=='percent')?$basePrice * $option->getPrice() / 100 : $option->getPrice());
                            	$option->setTitle(($optionTotalQty>1?$optionTotalQty.' x ':'') . $option->getTitle() . ($price>0?' - '.Mage::helper('customoptions')->currencyByStore($price, $store, true, false):''));
                            	$option->setUpdateTitleFlag(1);
                            }
                            break;
                    }
                    $product->addOption($option);                                        

                    switch ($option->getType()) {
                        case 'field':
                        case 'area':
                        case 'file':
                        case 'date':
                        case 'date_time':
                        case 'time':
                            $finalPrice += $this->_getCustomOptionsChargableOptionPrice($option->getPrice(), $option->getPriceType() == 'percent', $basePrice, $qty, $option->getCustomoptionsIsOnetime());
                            break;                        
                        default: //multiple
                            $optionQty = 1;
                        case 'drop_down':
                        case 'radio':
                        case 'checkbox':
                            $quoteItemOption = $product->getCustomOption('option_' . $option->getId());
                            $group = $option->groupFactory($option->getType())->setOption($option)->setQuoteItemOption($quoteItemOption);                            
                            $finalPrice += $group->getOptionPrice($quoteItemOption->getValue(), $basePrice, $qty, $optionQty);                            
                    }
                }
            }
        }        
        return $finalPrice;
    }

    protected function _getCustomOptionsChargableOptionPrice($price, $isPercent, $basePrice, $qty = 1, $customoptionsIsOnetime = 0) {
        $sub = 1;
        if ($customoptionsIsOnetime) {
            $sub = $qty;
        }
        if ($isPercent) {
            return ($basePrice * $price / 100) / $sub;
        } else {
            return $price / $sub;
        }
    }

}