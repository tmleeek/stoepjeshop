<?php 
/**
 * Country grid filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Widget_Grid_Column_Filter_Country extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Country
{
    protected function _getOptions()
    {      
       $controllerName 	= Mage::app()->getRequest()->getControllerName();
       $actionName     	= Mage::app()->getRequest()->getActionName(); 
       $country_id 		= Mage::getSingleton('admin/session')->getUser()->getRole()->getData('role_name');

       if('adminhtml_qquoteadv' == $controllerName && 'index' == $actionName){
           //checking admin role to have filter by country
		   if(strlen((string)$country_id)<=3){	 
		  	$options[] = array('value'=>$country_id, 'label'=>$country_id);
		   }else{
		   	$options = Mage::getResourceModel('directory/country_collection')->load()->toOptionArray(false);
	        array_unshift($options, array('value'=>'', 'label'=>Mage::helper('cms')->__('All Countries')));
		   }	
       }else{
       	   	$options = Mage::getResourceModel('directory/country_collection')->load()->toOptionArray(false);
	        array_unshift($options, array('value'=>'', 'label'=>Mage::helper('cms')->__('All Countries')));
       }	  
        
       return $options;
    }
}