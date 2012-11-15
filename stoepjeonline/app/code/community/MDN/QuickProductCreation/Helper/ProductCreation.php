<?php

class MDN_QuickProductCreation_Helper_ProductCreation extends Mage_Core_Helper_Abstract
{
    //Create products
    public function createProduct($data)
    {
        //init product
        $product = mage::getModel('catalog/product');

        //check sku
        $sku = $data['sku'];
        if ($this->skuAlreadyExist($sku))
                throw new Exception($this->__('Sku %s already used', $sku));

        //init from settings
        $applyDefaultValuesAttributes = mage::helper('QuickProductCreation/ProductAttributes')->getApplyDefaultValueAttributes();
        foreach($applyDefaultValuesAttributes as $att)
        {
            $defaultValue = mage::helper('QuickProductCreation/ProductAttributes')->getAttributeDefaultValue($att->getAttributeCode());
            $product->setData($att->getAttributeCode(), $defaultValue);
        }

        //init from form
        foreach ($data as $key => $value)
        {
            $product->setData($key, $value);
        }

        //add websites
        $websites = mage::getStoreConfig('quickproductcreation/miscellaneous/websites');
        $product->setWebsiteIds(explode(',', $websites));

        //Save
        $product->save();

        //Add stock information
        $stockItem = mage::getModel('cataloginventory/stock_item');
        $stockItem->setstock_id(1);             //default stock
        $stockItem->assignProduct($product);
        $stockItem->setproduct_id($product->getId());
        $stockItem->setuse_config_manage_stock(0);
        $stockItem->setmanage_stock(mage::getStoreConfig('quickproductcreation/stock/manage_stock'));
        if (isset($data['qty']))
            $stockItem->setqty($data['qty']);
        $stockItem->save();

        //save & return
        return $product;
    }

    /**
     * Check if a sku is available
     * @param <type> $sku
     */
    protected function skuAlreadyExist($sku)
    {
        $productId = mage::getModel('catalog/product')->getIdBySku($sku);
        return ($productId != null);
    }
}
