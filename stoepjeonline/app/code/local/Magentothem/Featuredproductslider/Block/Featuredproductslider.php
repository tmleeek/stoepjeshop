<?php
class Magentothem_Featuredproductslider_Block_Featuredproductslider extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getFeaturedproductslider()     
    { 
        if (!$this->hasData('featuredproductslider')) {
            $this->setData('featuredproductslider', Mage::registry('featuredproductslider'));
        }
        return $this->getData('featuredproductslider');
    }
	public function getProducts()
    {
    	$storeId    = Mage::app()->getStore()->getId();
		$products = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addMinimalPrice()
			->addUrlRewrite()
			->addTaxPercents()			
			->addStoreFilter()
			->addAttributeToFilter("featured", 1);		
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('featuredproductslider');
		if (isset($config['featuredproductslider_config']) ) {
			$value = $config['featuredproductslider_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}