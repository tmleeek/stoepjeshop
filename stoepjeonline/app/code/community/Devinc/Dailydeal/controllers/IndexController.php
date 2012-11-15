<?php
class Devinc_Dailydeal_IndexController extends Mage_Core_Controller_Front_Action
{	 
    public function indexAction()
    {
		$dailydeal_collection = Mage::getModel('dailydeal/dailydeal')->getCollection()->setOrder('display_on', 'DESC')->setOrder('dailydeal_id', 'DESC');
		$run = true;
		
		$deal_id = 0;
		foreach ($dailydeal_collection as $dailydeal) {		
			$model = Mage::getModel('dailydeal/dailydeal');	
			
			$_product = Mage::getModel('catalog/product')->load($dailydeal->getProductId());	
			$stockItem = $_product->getStockItem();
				if ($stockItem->getIsInStock()) {
					$qty = 1;
				} else {
					$qty = 0;
				}
			
			$product_status = Mage::getModel('catalog/product')->load($dailydeal->getProductId())->getStatus();
			
			if (substr(Mage::getModel('core/date')->date('Y-m-d H:i:s'),0,10)==substr($dailydeal->getDisplayOn(),0,10) && $dailydeal->getStatus()!=2 && $run) {
				if ($qty!=0 && $product_status==1) {
					$model->setId($dailydeal->getId())
						  ->setStatus('3')
						  ->save();
					$deal_id = $dailydeal->getProductId();
					$run = false;
				} else {
					$model->setId($dailydeal->getId())
						  ->setStatus('2')
						  ->save();
				}
			} elseif (substr(Mage::getModel('core/date')->date('Y-m-d H:i:s'),0,10)==substr($dailydeal->getDisplayOn(),0,10) && $dailydeal->getStatus()==3) {
				if ($qty!=0 && $product_status==1) {
					$model->setId($dailydeal->getId())
						  ->setStatus('1')
						  ->save();
				} else {
					$model->setId($dailydeal->getId())
					  ->setStatus('2')
					  ->save();	
				}
			} elseif (substr(Mage::getModel('core/date')->date('Y-m-d H:i:s'),0,10)>substr($dailydeal->getDisplayOn(),0,10)) {
				$model->setId($dailydeal->getId())
					  ->setStatus('2')
					  ->save();			
			}
			
			if (substr(Mage::getModel('core/date')->date('Y-m-d H:i:s'),0,10)>substr($dailydeal->getDisplayOn(),0,10) && $dailydeal->getStatus()==2 && $dailydeal->getDisable()==2) {
				$model->setId($dailydeal->getId())
					  ->setDisable('1') 
					  ->save();		
				//$store_id = $this->helper('core')->getStoreId();
				$store_id = 0;
				Mage::getModel('catalog/product_status')->updateProductStatus($dailydeal->getProductId(), $store_id, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
		}
		
        if ($deal_id!=0 && Mage::getStoreConfig('dailydeal/configuration/enabled')) {
            $_product = Mage::getModel('catalog/product')->load($deal_id);							
			header('Location: '.$_product->getProductUrl());
			exit;
        } elseif (Mage::getStoreConfig('dailydeal/configuration/enabled')) {
			Mage::getSingleton('catalog/session')->addError(Mage::helper('dailydeal')->__(Mage::getStoreConfig('dailydeal/configuration/no_deal_message')));	
			if (Mage::getStoreConfig('dailydeal/configuration/notify')) {			
				$mail = new Zend_Mail();
				$content = 'A customer tried to view today\'s deal.';
				$mail->setBodyHtml($content);
				$mail->setFrom('customer@dailydeal.com');
				$mail->addTo(Mage::getStoreConfig('dailydeal/configuration/admin_email'));
				$mail->setSubject('There is no deal set up for today.');	
				$mail->send();
			}
			$this->_redirect('');
        } else {
			$this->_redirect('no-route');		
		}
    }
}