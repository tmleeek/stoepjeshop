<?php

class MageWorx_MultiFees_Model_Catalog_Product_Attribute_Source_Additional_Fees extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    public function getAllOptions() {
        if (!$this->_options) {
            $collection = Mage::getResourceSingleton('multifees/fee_collection')
                ->addStatusFilter()
                ->addCheckoutTypeFilter(true)
                ->addSortOrderFilter()
                ->load();
            $this->_options = array();
            $this->_options[] = array('value' => '-1', 'label' => 'Default');
            $this->_options[] = array('value' => '-2', 'label' => 'None');
            foreach($collection as $fee) {
                $this->_options[] = array('value' => $fee->getFeeId(), 'label' => $fee->getTitle());
            }
        }
        return $this->_options;
    }
}