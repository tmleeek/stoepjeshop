<?php
class Magentothem_Bestsellerproductvertscroller_Block_Bestsellerproductvertscroller extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBestsellerproductvertscroller()     
     { 
        if (!$this->hasData('bestsellerproductvertscroller')) {
            $this->setData('bestsellerproductvertscroller', Mage::registry('bestsellerproductvertscroller'));
        }
        return $this->getData('bestsellerproductvertscroller');
        
    }
	public function getProducts()
    {
    	$storeId    = Mage::app()->getStore()->getId();
    	$products = Mage::getResourceModel('reports/product_collection')
    		->addOrderedQty()
            ->addAttributeToSelect('*')
			->addMinimalPrice()
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc');	
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('bestsellerproductvertscroller');
		if (isset($config['bestsellerproductvertscroller_config']) ) {
			$value = $config['bestsellerproductvertscroller_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}