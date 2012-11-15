<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_Model_Config_Mode
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'animation1', 'label'=>Mage::helper('adminhtml')->__('Animation1')),
            array('value'=>'animation2', 'label'=>Mage::helper('adminhtml')->__('Animation2')),
            array('value'=>'animation3', 'label'=>Mage::helper('adminhtml')->__('Animation3')),
            array('value'=>'animation4', 'label'=>Mage::helper('adminhtml')->__('Animation4')),
            array('value'=>'animation5', 'label'=>Mage::helper('adminhtml')->__('Animation5')),
            array('value'=>'animation6', 'label'=>Mage::helper('adminhtml')->__('Animation6')),            
        );
    }

}
