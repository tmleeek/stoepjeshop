<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_Block_Adminhtml_Banner1 extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banner1';
    $this->_blockGroup = 'banner1';
    $this->_headerText = Mage::helper('banner1')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('banner1')->__('Add Item');
    parent::__construct();
  }
}