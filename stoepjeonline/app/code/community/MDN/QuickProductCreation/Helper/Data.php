<?php

class MDN_QuickProductCreation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_REQUIRED_EDIT_ATTRIBUTES = 'quickproductcreation/required_edit_attributes';

    protected $_requiredAttributes = null;

    public function attributeIsRequiredForEdit($attributeCode)
    {
        $requiredAttributes = $this->getRequiredAttributes();

        return array_key_exists($attributeCode, $requiredAttributes);
    }

    protected function getRequiredAttributes()
    {
        if ($this->_requiredAttributes == null)
        {
             $this->_requiredAttributes = Mage::getConfig()
                ->getNode(self::XML_PATH_REQUIRED_EDIT_ATTRIBUTES)
                ->asArray();
        }

        return $this->_requiredAttributes;
    }

    public function displayStockLevelInput()
    {
        if (!mage::getStoreConfig('quickproductcreation/stock/manage_stock'))
            return false;
        
        return (mage::getStoreConfig('quickproductcreation/stock/display_stock_level_input') == 1);
    }


    public function displayNotifyQtyInput()
    {
        if (!mage::getStoreConfig('quickproductcreation/stock/manage_stock'))
            return false;

        return (mage::getStoreConfig('quickproductcreation/stock/display_notify_qty_input') == 1);
    }

    public function displayBackordersInput()
    {
        if (!mage::getStoreConfig('quickproductcreation/stock/manage_stock'))
            return false;

        return (mage::getStoreConfig('quickproductcreation/stock/display_backorders_input') == 1);
    }

}