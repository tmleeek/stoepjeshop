<?php

class MageWorx_MultiFees_Model_Catalog_Product_Attribute_Backend_Additional_Fees extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract {
    public function beforeSave($object) {
        $attributeName = $this->getAttribute()->getName();

        $additionalFees = $object->getData($attributeName);
        if (is_array($additionalFees) && count($additionalFees)) {
            $data = implode(',', $additionalFees);
            $object->setData($attributeName, $data);
        } else {
            $object->setData($attributeName, '');
        }
    }
}