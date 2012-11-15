<?php
class Magentothem_Newproduct_Block_Newproduct extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getNewproduct()     
    { 
        if (!$this->hasData('newproduct')) {
            $this->setData('newproduct', Mage::registry('newproduct'));
        }
        return $this->getData('newproduct');
    }
	public function getProducts()
    {
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    	$storeId    = Mage::app()->getStore()->getId();
		$products = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addMinimalPrice()
			->addUrlRewrite()
			->addTaxPercents()			
			->addStoreFilter()
			->addAttributeToFilter('news_from_date', array('date'=>true, 'to'=> $todayDate))
			->addAttributeToFilter(array(array('attribute'=>'news_to_date', 'date'=>true, 'from'=>$todayDate), array('attribute'=>'news_to_date', 'is' => new Zend_Db_Expr('null'))),'','left')
			->setOrder($this->getConfig('sort'),$this->getConfig('direction'))
			->addAttributeToSort('news_from_date','desc');		
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('newproduct');
		if (isset($config['newproduct_config']) ) {
			$value = $config['newproduct_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}