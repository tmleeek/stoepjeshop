<?php

class MDN_QuickProductCreation_Block_Admin_Result extends Mage_Core_Block_Template
{
    public function getBackUrl()
    {
        return mage::helper('adminhtml')->getUrl('QuickProductCreation/Admin/Form');
    }

    public function getProductUrl($_product)
    {
        return mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $_product->getId()));
    }
}