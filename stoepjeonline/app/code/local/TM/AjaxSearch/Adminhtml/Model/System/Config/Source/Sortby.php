<?php
/*AjaxSearch*/
class TM_AjaxSearch_Adminhtml_Model_System_Config_Source_Sortby
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'name', 'label'=>Mage::helper('ajaxsearch')->__('Product name')),
            array('value'=>'price', 'label'=>Mage::helper('ajaxsearch')->__('Product base price')),
			array('value'=>'sku', 'label'=>Mage::helper('ajaxsearch')->__('Product SKU'))
        );
    }
}