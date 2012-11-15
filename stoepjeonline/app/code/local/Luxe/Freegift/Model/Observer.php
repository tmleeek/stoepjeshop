<?php
/**
 * Luxe 
 *
 * @category   Luxe 
 * @package    Luxe_Freegift
 * @copyright  Copyright (c) 2009-2010 Luxe Soft
 */

class Luxe_Freegift_Model_Observer {
    const NO_RULES = 6;

    public function sales_quote_save_after($observer) {
        if (Mage::getStoreConfig('freegift/freegift/active')) {
            $this->checkCart($observer); 
        }
    }

    private function executeRule($rule) {
        $cart = Mage::getSingleton('checkout/cart');

        $gift_exists = false;
        $total_qty = 0;
        $total_amount = 0;
        $is_mandatory_exists = false;
        $is_nonmandatory_exists = false;

        foreach ($cart->getItems() as $item) {
            if ($item->getProductId() == $rule['gift_product_id']) {
                if ($item->getQty() > 1) {
                    $item->setQty(1);
                    $cart->save();
                }
                $gift_exists = $item->getId();
                continue;
            }

            if ($item->getProduct()->getSku() == $rule['mandatory_item']) {
                $is_mandatory_exists = true;
                //if more then one mandatory item purchased, count them as non-mandatory item
                if ($item->getQty() > 1) {
                    $is_nonmandatory_exists = true;
                }
            }
            if (in_array($item->getProduct()->getSku(), $rule['nonmandatory_items'])) {
                $is_nonmandatory_exists = true;
            }


            $qty = $item->getQty();
            if ($item->getParentItem()) {
                $qty *= $item->getParentItem()->getQty();
                $category_ids = $item->getParentItem()->getProduct()->getCategoryIds();
            } else {
                $category_ids = $item->getProduct()->getCategoryIds();        
            }

            $intersec = array_intersect($rule['allowed_cats'], $category_ids); 
            if (empty($rule['allowed_cats']) || !empty($intersec)) {
                $total_amount += $item->getCalculationPrice() * $item->getQty();    
                $total_qty += $qty;
            }
        }

        if ( !$rule['mandatory_item'] && 
                (($total_qty >=  $rule['min_purchased_qty'] && $rule['min_purchased_qty']) 
                    || ($total_amount >= $rule['min_purchased_amount'] && $rule['min_purchased_amount']))
                || ($is_mandatory_exists && $is_nonmandatory_exists)) {

            if (!$gift_exists) {       
                $gift = Mage::getModel('catalog/product')->load($rule['gift_product_id']);
                if ($gift->getStockItem()->getQty() < 1) {
                	return false;
                }
                $cart->addProduct($gift, array());    
                foreach ($cart->getItems() as $item) {
                    if ($item->getProductId() == $rule['gift_product_id']) {
                        $item->setCustomPrice(0);
                        $item->setOriginalCustomPrice(0);
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }

                            	                    	                                                                                                                
                $cart->getQuote()->collectTotals();
                $cart->getQuote()->save();

                $session = Mage::getSingleton('checkout/session');
                $session->setCartWasUpdated(true);
                $session->addSuccess($rule['gift_message']);
            }
            return true;
        } else {
            if ($gift_exists) {
                $cart->removeItem($gift_exists);       
                $cart->getQuote()->collectTotals();
                $cart->getQuote()->save();
            }
            return false;
        }
        
    }

    public function checkCart($observer) {
        static $already_in_event = false;

        if ($already_in_event) {
            return;
        }
        $already_in_event = true;

        for ($i = 1; $i < self::NO_RULES + 1; $i++) {
            $rule = array();
            
            if ($i == 1) {
                $rule['allowed_cats']         = explode(',', Mage::getStoreConfig('freegift/freegift/category_filter'));
                $rule['gift_product_id']      = intval(Mage::getStoreConfig('freegift/freegift/gift_product_id'));
                $rule['mandatory_item']       = Mage::getStoreConfig('freegift/freegift/mandatory_item');
                $rule['nonmandatory_items']   = explode(',', Mage::getStoreConfig('freegift/freegift/nonmandatory_items'));
                $rule['min_purchased_qty']    = intval(Mage::getStoreConfig('freegift/freegift/min_purchased_qty'));
                $rule['min_purchased_amount'] = floatval(Mage::getStoreConfig('freegift/freegift/min_purchased_amount'));
                $rule['gift_message']         = Mage::getStoreConfig('freegift/freegift/gift_message');
            } else {
                if (!Mage::getStoreConfig('freegift/compaign'.$i.'/active')) {
                    continue;
                }
                $rule['allowed_cats']         = explode(',', Mage::getStoreConfig('freegift/compaign'.$i.'/category_filter'));
                $rule['gift_product_id']      = intval(Mage::getStoreConfig('freegift/compaign'.$i.'/gift_product_id'));
                $rule['mandatory_item']       = Mage::getStoreConfig('freegift/compaign'.$i.'/mandatory_item');
                $rule['nonmandatory_items']   = explode(',', Mage::getStoreConfig('freegift/compaign'.$i.'/nonmandatory_items'));
                $rule['min_purchased_qty']    = intval(Mage::getStoreConfig('freegift/compaign'.$i.'/min_purchased_qty'));
                $rule['min_purchased_amount'] = floatval(Mage::getStoreConfig('freegift/compaign'.$i.'/min_purchased_amount'));
                $rule['gift_message']         = Mage::getStoreConfig('freegift/compaign'.$i.'/gift_message');
            }
            $rule['allowed_cats'] = array_filter($rule['allowed_cats'], 'intval');
            if ($this->executeRule($rule)) {
                //if one rule is executed successfully then we dont apply other rules
                break;
            }
        }
        
        $already_in_event = false;

    }
}
