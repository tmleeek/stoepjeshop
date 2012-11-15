<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_Block_Banner1 extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBanner1()     
     { 
        if (!$this->hasData('banner1')) {
            $this->setData('banner1', Mage::registry('banner1'));
        }
        return $this->getData('banner1');
        
    }
	public function getDataBanner1()
    {
    	$resource = Mage::getSingleton('core/resource');
		$read= $resource->getConnection('core_read');
		$slideTable = $resource->getTableName('banner1');	
		$select = $read->select()
		   ->from($slideTable,array('banner1_id','title','link','description','image','status'))
		   ->where('status=?',1);
		$slide = $read->fetchAll($select);	
		return 	$slide;			
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('banner1');
		if (isset($config['banner1_config']) ) {
			$value = $config['banner1_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}