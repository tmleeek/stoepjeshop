<?php

class Ophirah_Qquoteadv_Model_Observer
{
      /**
     * Change status to Request expired 
     */
	public function updateStatusRequest()
    {        
        $day = 2;
	    $now = Mage::getSingleton('core/date')->gmtDate(); 
	    $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
	    $collection->addFieldToFilter('status', Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST);
	    $collection->getSelect()
        ->where('created_at<INTERVAL -'.$day.' DAY + \''.$now.'\'');
        $collection->load();
       

        foreach ($collection as $item) {               
                $item->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_EXPIRED);
                $item->save();  
        }
    }
    
     /**
     * Change status to Proposal expired 
     */
	public function updateStatusProposal()
    {        
        $day = 3;
	    $now = Mage::getSingleton('core/date')->gmtDate(); 
	    $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
	    $collection->addFieldToFilter('status', Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SENT);
	    $collection->getSelect()
        ->where('created_at<INTERVAL -'.$day.' DAY + \''.$now.'\'');
        $collection->load();       

        foreach ($collection as $item) {               
                $item->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_EXPIRED);
                $item->save();  
        }
    }

     /**
     * Switch between default layout and c2q module layout
     */
	public function switchQuoteLayout( $observer ){
      $updatesRoot =  $observer->getUpdates();
      $moduleName = 'qquoteadv';
      $enabled = Mage::getStoreConfig('qquoteadv/general/enabled', Mage::app()->getStore()->getStoreId());
      if($enabled && !Mage::getStoreConfig('qquoteadv/debug/active_c2q_tmpl') && !Mage::app()->getStore()->isAdmin() ){
        foreach ($updatesRoot->children() as $updateNode) {
          if( $moduleName == $updateNode->getName()){
            $dom=dom_import_simplexml($updateNode);
            $dom->parentNode->removeChild($dom);
          }
        }
      }
      return $this;
    }

	public function setCartQuoteMode( $observer ){
		Mage::getSingleton('core/session')->cartquotemode = 'cart';
		return $this;
	}    
    
    public function setCustomPrice( $observer ){ 
        $customPrice = Mage::registry('customPrice');
        if( isset($customPrice) ){
            if(Mage::helper('customer/data')->isLoggedIn() || Mage::getSingleton('admin/session')->isLoggedIn() ){
                $event = $observer->getEvent();
                $quote_item = $event->getQuoteItem();
                
                $currentVersion = Mage::getVersion();
                if (version_compare($currentVersion, '1.6.1.0')<0) {   
                    $quote_item->setOriginalCustomPrice($customPrice);                         
                }else{
                    $quote_item->setCustomPrice($customPrice); 
                }
                
                try{
                    $quote_item->save();
                } catch (Exception $e) { 
                   Mage::log($e->getMessage());
                }
            }
            
            //#! use this code to avoid case when simple products from bundle/configurable product added into shopping cart 
            // independent of parent product
            Mage::unregister('customPrice');
        }
		return $this;
	}
    
    public function disableRemoveQuoteItem(Varien_Event_Observer $observer ){
		if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {
			$product = $observer->getQuoteItem();
			$product->isDeleted(false);
			
			$message =  Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
            Mage::getSingleton('checkout/session')->addError($message);
		}
		return $this;
	}
    //#log out from quote confirmation mode
    public function logoutFromQuoteConfirmationMode(Varien_Event_Observer $observer ) {
        
        if ( Mage::helper('qquoteadv')->isActiveConfirmMode()) {            
            Mage::helper('qquoteadv')->setActiveConfirmMode(false); 
        }
     }

    public function disableQtyUpdate(Varien_Event_Observer $observer ) {
        if ( Mage::helper('qquoteadv')->isActiveConfirmMode()) {
			$cartData = Mage::app()->getRequest()->getParam('cart');
			foreach ($cartData as $index => $data) {
				if (isset($data['qty'])) {
					$cartData[$index]['qty'] = null;
				}
			}
			Mage::app()->getRequest()->setParam('cart', $cartData);

			$link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
			$message = Mage::helper('qquoteadv')->__("To update item in the Shopping cart <a href='%s'>log out</a> from Quote confirmation mode.",$link);
            Mage::getSingleton('checkout/session')->addNotice($message);
        }

     }

    public function disableUpdateItemOptions(Varien_Event_Observer $observer ) {   
        if ( Mage::helper('qquoteadv')->isActiveConfirmMode()) {		   

			Mage::app()->getRequest()->setParam('id', null);

            $message =  Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
            Mage::getSingleton('checkout/session')->addError($message);

			$link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
			$message = Mage::helper('qquoteadv')->__("To update item in the Shopping cart <a href='%s'>log out</a> from Quote confirmation mode.",$link);
            Mage::getSingleton('checkout/session')->addNotice($message);

        }
     }

     public function disableAddProduct(Varien_Event_Observer $observer ) {
        if ( Mage::helper('qquoteadv')->isActiveConfirmMode()) {
			
			Mage::app()->getRequest()->setParam('product', null);

            $message =  Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
            Mage::getSingleton('checkout/session')->addError($message);

			$link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
			$message = Mage::helper('qquoteadv')->__("To update item in the Shopping cart <a href='%s'>log out</a> from Quote confirmation mode.",$link);
            Mage::getSingleton('checkout/session')->addNotice($message);
        }
     }
}