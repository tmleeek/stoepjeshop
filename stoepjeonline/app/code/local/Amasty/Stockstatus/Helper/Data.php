<?php
/**
* @copyright Amasty.
*/
class Amasty_Stockstatus_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function show($product)
    {
        return Mage::app()->getLayout()->createBlock('amstockstatus/status')->setProduct($product)->toHtml();
    }
    
    public function processViewStockStatus($product, $html)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        if ( (!Mage::getStoreConfig('catalog/general/displayforoutonly') || !$product->isSaleable()) || ($product->isInStock() && $stockItem->getData('qty') <= Mage::helper('amstockstatus')->getBackorderQnt() ) )
        {
            if (Mage::helper('amstockstatus')->getCustomStockStatusText($product))
            {
                //translated labels
                if ('bundle' == $product->getTypeId())
                {
                    $inStock   = Mage::helper('amstockstatus')->__('<span class=\"value\">In stock<\/span>');
                    $outStock  = Mage::helper('amstockstatus')->__('<span class=\"value\">Out of stock<\/span>');
                } else 
                {
                    $inStock   = Mage::helper('amstockstatus')->__('In stock');
                    $outStock  = Mage::helper('amstockstatus')->__('Out of stock');
                }

                                                                // leave empty space here
                                                                //            v
                $status = Mage::getStoreConfig('catalog/general/icononly') ? ' ' : Mage::helper('amstockstatus')->getCustomStockStatusText($product);

                if ($status)
                {
                    if (Mage::getStoreConfig('catalog/general/icononly') || $product->getData('hide_default_stock_status') || (!$product->isConfigurable() && $product->isInStock() && $stockItem->getManageStock() && 0 == $stockItem->getData('qty')))
                    {
                        $html = preg_replace("@($inStock|$outStock)[\s]*<@", '' . $status . '<', $html);
                    }
                    else 
                    {
                        $html = preg_replace("@($inStock|$outStock)[\s]*<@", '$1 ' . $status . '<', $html);
                    }
                }
                
                // adding icon if any
                $availability = Mage::helper('amstockstatus')->__('Availability:');
                $html = str_replace($availability, $availability . $this->getStatusIconImage($product), $html);
            }
        }
        return $html;
    }
    
    public function getStatusIconImage($product)
    {
        $iconHtml = '';
        if ($iconUrl = $this->getStatusIconUrl(Mage::helper('amstockstatus')->getCustomStockStatusId($product)))
        {
            $iconHtml = ' <img src="' . $iconUrl . '" class="amstockstatus_icon" alt=""> ';
        }
        return $iconHtml;
    }
    
    public function getCustomStockStatusText(Mage_Catalog_Model_Product $product)
    {
        $status      = '';
        $rangeStatus = Mage::getModel('amstockstatus/range');
        
        if ($product->getData('custom_stock_status_quantity_based'))
        {
            $stockItem   = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $rangeStatus->loadByQty($stockItem->getData('qty'));
        }
        
        if ($rangeStatus->hasData('status_id'))
        {
            // gettins status for range
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'custom_stock_status');
            foreach ( $attribute->getSource()->getAllOptions(true, true) as $option )
            {
                if ($rangeStatus->getData('status_id') == $option['value'])
                {
                    $status = $option['label'];
                    break;
                }
            }
        } elseif (!Mage::getStoreConfig('catalog/general/userangesonly')) 
        {
            $status = $product->getAttributeText('custom_stock_status');
        }
        
        return $status;
    }
    
    public function getCustomStockStatusId(Mage_Catalog_Model_Product $product)
    {
        $statusId    = null;
        $rangeStatus = Mage::getModel('amstockstatus/range');
        
        if ($product->getData('custom_stock_status_quantity_based'))
        {
            $stockItem   = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $rangeStatus->loadByQty($stockItem->getData('qty'));
        }
        
        if ($rangeStatus->hasData('status_id'))
        {
            $statusId = $rangeStatus->getData('status_id');
        } elseif (!Mage::getStoreConfig('catalog/general/userangesonly')) 
        {
            $statusId = $product->getData('custom_stock_status');
        }
        
        return $statusId;
    }
    
    public function getBackorderQnt()
    {
        return 0;
    }
    
    public function getStatusIconUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'amstockstatus' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . '/' . 'amstockstatus' . '/' . 'icons' . '/' . $optionId . '.jpg';
        }
        return '';
    }
}