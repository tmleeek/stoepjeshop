<?php

class MageWorx_CustomOptions_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0) {
        $result = new Varien_Object();
        $result->setHasError(false);

        if (!is_numeric($qty)) {
            $qty = Mage::app()->getLocale()->getNumber($qty);
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());

        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = Mage::app()->getLocale()->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && ($qty) < $this->getMinSaleQty()) {
            $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1))
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                    ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($this->getMaxSaleQty() && ($qty) > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1))
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                    ->setQuoteMessageIndex('qty');
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {
            $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('This product is currently out of stock.'))
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are currently out of stock'))
                    ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
            $result->addData($this->checkQtyIncrements($qty)->getData());
        }

        if ($result->getHasError()) {
            return $result;
        }

        // outOfStock
        $options = Mage::app()->getRequest()->getParam('options', false);       
        $outOfStock = false;        
        
        if ($options && Mage::helper('customoptions')->isInventoryEnabled()) {
            foreach ($options as $optionId => $option) {                
                $productOption = Mage::getModel('catalog/product_option')->load($optionId);
                if (!is_array($option) && ($productOption->getType()=='drop_down' || $productOption->getType()=='radio')) $option = array($option);                
                foreach ($option as $optionTypeId) {
                    $row = $productOption->getOptionValue($optionTypeId);
                    //print_r($row); exit;
                    if (isset($row['customoptions_qty']) && $row['customoptions_qty']!=='') {
                        $post = Mage::app()->getRequest()->getParams();                         
                        switch ($productOption->getType()) {
                            case 'checkbox':                            
                                if (isset($post['options_'.$optionId.'_'.$optionTypeId.'_qty'])) $optionQty = intval($post['options_'.$optionId.'_'.$optionTypeId.'_qty']); else $optionQty = 1;
                                break;
                            case 'drop_down':
                            case 'radio':                                                    
                                if (isset($post['options_'.$optionId.'_qty'])) $optionQty = intval($post['options_'.$optionId.'_qty']); else $optionQty = 1;
                                break;
                            case 'multiple':
                                $optionQty = 1;                            
                                break;                       
                        }                        
                        $optionTotalQty = ($productOption->getCustomoptionsIsOnetime()?$optionQty:$optionQty*$qty);                        
                        if ($row['customoptions_qty']<$optionTotalQty) {
                            $outOfStock = true;
                            break; break;
                        }                            
                    }
                }                                        
                
            }
        }        
        
        if ($outOfStock || !$this->checkQty($summaryQty)) {
            $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
            $result->setHasError(true)
                    ->setMessage($message)
                    ->setQuoteMessage($message)
                    ->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if (($this->getQty() - $summaryQty) < 0) {
                if ($this->getProductName()) {
                    if ($this->getIsChildItem()) {
                        $backorderQty = ($this->getQty() > 0) ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    } else {
                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = ($this->getQty() > $orderedItems) ? ($this->getQty() - $orderedItems) * 1 : 0;
                        $backorderQty = ($itemsLeft > 0) ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(Mage::helper('cataloginventory')->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1)));
                        } else {
                            $result->setMessage(Mage::helper('cataloginventory')->__('"%s" is not available in the requested quantity. %s of the items will be backordered.', $this->getProductName(), ($backorderQty * 1)));
                        }
                    }
                }
            }
        }

        return $result;
    }

}
