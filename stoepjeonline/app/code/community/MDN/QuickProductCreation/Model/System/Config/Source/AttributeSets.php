<?php

class MDN_QuickProductCreation_Model_System_Config_Source_AttributeSets extends Mage_Core_Model_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {

                $options = array();

                $collection = mage::helper('QuickProductCreation/ProductAttributes')->getAttributeSets();

                foreach ($collection as $attributeSet)
                {
                    $options[] = array('value' => $attributeSet->getId(),
                                       'label' => $attributeSet->getattribute_set_name());
                }
                $this->_options = $options;

        }
        return $this->_options;
    }

        public function toOptionArray()
        {
                return $this->getAllOptions();
        }
}