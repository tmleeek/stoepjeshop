<?php
class Devinc_Dailydeal_Block_Dailydeal extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    } 
    
	
    public function getIsDaily()
    {		
        $collection = Mage::getModel('dailydeal/dailydeal')->getCollection()->addFieldToFilter('status', 3);
		$product_id = '';
		foreach ($collection as $prod) {
			$product_id = $prod->product_id;
			$dailydeal_id = $prod->dailydeal_id;
		}   
		return $product_id;
	}
	
    public function getNrViews()
	{
		$collection = Mage::getModel('dailydeal/dailydeal')->getCollection()->addFieldToFilter('status', 3);
		foreach ($collection as $prod) {
			$dailydeal_id = $prod->dailydeal_id;
		}
		$model = Mage::getModel('dailydeal/dailydeal');	
		$nr_views = $model->load($dailydeal_id)->getNrViews();
		$nr_views++;
		$model->setId($dailydeal_id)
			  ->setNrViews($nr_views)
			  ->save();		
	}
	
    public function getDealQty()
    {		
		$collection = Mage::getModel('dailydeal/dailydeal')->getCollection()->addFieldToFilter('status', 3);
		foreach ($collection as $prod) {
			$product_id = $prod->product_id;
		}  
		$_product = Mage::getModel('catalog/product')->load($product_id);	
		
        return number_format(Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty(),0);
	} 
	
	public function getPageUrl() {
		$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
		$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
		$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
		$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
		return $url;
	}
	
     public function getDailydeal()     
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
	 
        return $deal_id;
		
    }
}