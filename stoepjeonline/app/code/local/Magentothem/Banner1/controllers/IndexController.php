<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/banner1?id=15 
    	 *  or
    	 * http://site.com/banner1/id/15 	
    	 */
    	/* 
		$banner1_id = $this->getRequest()->getParam('id');

  		if($banner1_id != null && $banner1_id != '')	{
			$banner1 = Mage::getModel('banner1/banner1')->load($banner1_id)->getData();
		} else {
			$banner1 = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($banner1 == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$banner1Table = $resource->getTableName('banner1');
			
			$select = $read->select()
			   ->from($banner1Table,array('banner1_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$banner1 = $read->fetchRow($select);
		}
		Mage::register('banner1', $banner1);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}