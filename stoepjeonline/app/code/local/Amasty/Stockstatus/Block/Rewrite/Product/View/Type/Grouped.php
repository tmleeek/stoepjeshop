<?php
/**
* @copyright Amasty.
*/
class Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped
{
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        $html = $this->helper('amstockstatus')->processViewStockStatus($this->getProduct(), $html);
        return $html;
    }
}