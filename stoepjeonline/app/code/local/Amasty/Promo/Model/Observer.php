<?php
/**
 * @copyright   Copyright (c) 2009-11 Amasty
 */
class Amasty_Promo_Model_Observer
{
    protected $_isHandled = array();
    /**
     * Process sales rule form creation
     * @param   Varien_Event_Observer $observer
     */
    public function handleFormCreation($observer)
    {
        $actionsSelect = $observer->getForm()->getElement('simple_action');
        if ($actionsSelect){
            $vals = $actionsSelect->getValues();
            $vals[] = array(
                'value' => 'ampromo_items',
                'label' => Mage::helper('ampromo')->__('Auto add promo items with products'),
                
            );
            $vals[] = array(
                'value' => 'ampromo_cart',
                'label' => Mage::helper('ampromo')->__('Auto add promo items for the whole cart'),
                
            );
            $vals = $vals;
            $actionsSelect->setValues($vals);
            $actionsSelect->setOnchange('ampromo_hide()');
            
            $fldSet = $observer->getForm()->getElement('action_fieldset');
            $fldSet->addField('promo_sku', 'text', array(
                'name'     => 'promo_sku',
                'label' => Mage::helper('ampromo')->__('Promo Items'),
                'note'  => Mage::helper('ampromo')->__('Comma separated list of the SKUs'),
                ),
                'discount_amount'
            );            
        }
        
        return $this; 
    }
    
    /**
     * Process quote item validation and discount calculation
     * @param   Varien_Event_Observer $observer
     */
    public function handleValidation($observer) 
    {
        $rule = $observer->getEvent()->getRule();
        if (!in_array($rule->getSimpleAction(), array('ampromo_items','ampromo_cart'))){
            return $this;
        }
        
        if (isset($this->_isHandled[$rule->getId()])){
            return $this;
        }
        $this->_isHandled[$rule->getId()] = true;
        
        $promoSku = $rule->getPromoSku();
        if (!$promoSku){
            return $this;     
        }  
        
        $quote = $observer->getEvent()->getQuote();
        $qty = $this->_getFreeItemsQty($rule, $quote);
        if (!$qty){
            //@todo  - add new field for label table
            // and show message like "Add 2 more products to get free items"
            return $this;         
        }
            
		$promoSku = explode(',', $promoSku);
		foreach ($promoSku as $sku){
		    $sku = trim($sku);
		    if (!$sku){
		        continue;
		    }
		    $this->_showMessage($rule->getStoreLabel(Mage::app()->getStore()), false);
   				    
            $product = $this->_loadProduct($sku, $qty);
            if (!$product){
                continue;
            }
            
            $this->_addProductToQuote($quote, $product, $qty);
		}
        return $this;
    }
    
    public function initFreeItems($observer) 
    { 
        $this->_isHandled = array();
        
        $quote = $observer->getQuote();
        if (!$quote) 
            return $this;
            
        foreach ($quote->getItemsCollection() as $item) {
            if (!$item){
                continue;
            }
                
            if (!$item->getOptionByCode('ampromo_rule')){
                continue;
            }
             
            $item->isDeleted(true);
            $item->setData('qty_to_add', '0.0000');
            $quote->removeItem($item->getId());
        }
        return $this;
    }
    
    public function updateFreeItems($observer) 
    { 
        $info = $observer->getInfo();
        $quote = $observer->getCart()->getQuote();
        foreach (array_keys($info) as $itemId) {
            $item = $quote->getItemById($itemId);
            if (!$item) 
                continue;
                
            if (!$item->getOptionByCode('ampromo_rule')) 
                continue;
                
            if (empty($info[$itemId]))
                continue;
                
            $info[$itemId]['remove'] = true;
        }
        
        return $this;
    }  
    
    // find qty 
    // (for the whole cart it is $rule->getDiscountQty()
    // for items it is (qty * (number of matched non-free items) / step)
    protected function _getFreeItemsQty($rule, $quote)
    {  
        $amount = max(1, $rule->getDiscountAmount());
        $qty    = 0;
        if ('ampromo_cart' == $rule->getSimpleAction()){
            $qty = $amount;
        }
        else {
            $step = max(1, $rule->getDiscountStep());
            foreach ($quote->getItemsCollection() as $item) {
                if (!$item) 
                    continue;
                    
                if ($item->getOptionByCode('ampromo_rule')) 
                    continue;
                    
                if (!$rule->getActions()->validate($item)) {
                    continue;
                }
                
               $qty = $qty + $item->getQty();
            } 
            
            $qty = floor($qty / $step) * $amount; 
            $max = $rule->getDiscountQty();
            if ($max){
                $qty = min($max, $qty);
            }
        }
        return $qty;        
    }  
    
    protected function _loadProduct($sku, $qty)
    {
        //@todo add cache
	    $product = Mage::getModel('catalog/product')->reset();
	    $product->load($product->getIdBySku($sku)); // we have to load each product individually
	    
	    if (!$product->getId()){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but there is no promo item with the SKU `%s` in the catalog', $sku
            ));	
	        return false;
	    }
	    if (Mage_Catalog_Model_Product_Status::STATUS_ENABLED != $product->getStatus()){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but promo item with the SKU `%s` is not available', $sku
            ));		        
	        return false;
	    }
        $hasQty  = $product->getStockItem()->checkQty($qty);
        $inStock = $product->getStockItem()->getIsInStock();
        if (!$inStock || !$hasQty){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but there are no %d item(s) with the SKU `%s` in the stock', $qty, $sku
            ));
            return false;
        }
        return $product;        
    }    
    
    protected function _addProductToQuote($quote, $product, $qty)
    {
        try {
            $product->addCustomOption('ampromo_rule', 1);
            
            $item  = $quote->getItemByProduct($product);
            if ($item) {  
                return false;       
            }
            
            $item = $quote->addProduct($product, $qty);
            $item->setCustomPrice(0); 
            $item->setOriginalCustomPrice(0); 
            
            $prefix = Mage::getStoreConfig('ampromo/general/prefix');
            if ($prefix){
                $item->setName($prefix . ' ' . $item->getName());
            }
            
            $customMessage = Mage::getStoreConfig('ampromo/general/message');
            if ($customMessage){
                $item->setMessage($customMessage);
            }            
        }
        catch (Exception $e){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but there is an error while adding free items to the cart: %s', $e->getMessage()
            ));            
             return false;   
            
        }
        return true;        
    }
       
    protected function _showMessage($message, $isError = true) 
    { 
        // @todo
        // show on cart page only
        $all = Mage::getSingleton('checkout/session')->getMessages(false)->toString();
        if (false !== strpos($all, $message))
            return;
            
        if ($isError){
            Mage::getSingleton('checkout/session')->addError($message);
        }
        else {
            Mage::getSingleton('checkout/session')->addNotice($message);
        }
    }  
   
}