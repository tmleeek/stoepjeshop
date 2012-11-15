<?php
class Devinc_Dailydeal_Block_Adminhtml_Dailydeal extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_dailydeal';
    $this->_blockGroup = 'dailydeal';
    $this->_headerText = Mage::helper('dailydeal')->__('Deal Manager');
    $this->_addButtonLabel = Mage::helper('dailydeal')->__('Add Deal');
    parent::__construct();
	
	
	$dailydeal_collection = Mage::getModel('dailydeal/dailydeal')->getCollection()->setOrder('display_on', 'DESC')->setOrder('dailydeal_id', 'DESC');
	$run = true;

	
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
		
		$today = $dailydeal->getDisplayOn();
		$tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($today))).' 00:00:00';
		$sales_collection = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('created_at', array("from" =>  $today, "to" =>  $tomorrow, "datetime" => true))->addFieldToFilter('product_id', $dailydeal->getProductId());
		
		$model->setId($dailydeal->getId())
			  ->setQtySold(count($sales_collection))
			  ->save();	
	}
	
  }
}