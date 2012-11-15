<?php

class MDN_QuickProductCreation_Model_System_Config_Source_Websites extends Mage_Core_Model_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {

                $options = array();

                $collection = mage::getModel('core/website')->getCollection();

                foreach ($collection as $website)
                {
                    $options[] = array('value' => $website->getId(),
                                       'label' => $website->getname());
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