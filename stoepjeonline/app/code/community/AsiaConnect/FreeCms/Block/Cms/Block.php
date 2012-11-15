<?php
class AsiaConnect_FreeCms_Block_Cms_Block extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'cms_block';
	    $this->_blockGroup = 'freecms';
        $this->_headerText = Mage::helper('cms')->__('Free Blocks');
        $this->_addButtonLabel = Mage::helper('cms')->__('Add New Block');
        parent::__construct();
    }

}
