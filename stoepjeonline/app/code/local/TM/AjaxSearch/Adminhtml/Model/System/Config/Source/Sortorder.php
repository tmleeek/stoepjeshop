<?php
/*AjaxSearch*/
class TM_AjaxSearch_Adminhtml_Model_System_Config_Source_Sortorder
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'desc', 'label'=>Mage::helper('ajaxsearch')->__('Descending')),
            array('value'=>'asc', 'label'=>Mage::helper('ajaxsearch')->__('Ascending'))
        );
    }
}