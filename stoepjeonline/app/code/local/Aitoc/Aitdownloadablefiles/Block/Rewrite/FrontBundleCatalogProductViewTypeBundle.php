<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Block/Rewrite/FrontBundleCatalogProductViewTypeBundle.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ iqqMsSBqaeCoBZqa('ba0fbf153a1880c1b67f95e3c12127b8'); ?><?php
/**
 * @copyright  Copyright (c) 2010 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Block_Rewrite_FrontBundleCatalogProductViewTypeBundle extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle
{
    public function _toHtml()
    {
        $sHtml = parent::_toHtml();
        
        if (!Mage::registry('ait_usfiles_flag'))
        {
            Mage::register('ait_usfiles_flag', 1);
        
            $linksHtml = '';
            if (!$this->helper('aitdownloadablefiles')->isManualRendered())
            {
                $linksHtml = $this->_renderAitfilesLinks();
            }
            
            $sHtml  = $sHtml . $linksHtml;
        }
        
        return $sHtml;
    }

    public function getAitfilesLinks()
    {
        $linksHtml = '';
        if ($this->helper('aitdownloadablefiles')->isManualRendered())
        {
            $linksHtml = $this->_renderAitfilesLinks();
        }
        
        return $linksHtml;
    }
    
    protected function _renderAitfilesLinks()
    {
        $linksHtml = '';
        $renderBlock = $this->getLayout()->createBlock('aitdownloadablefiles/frontCatalogProductAitfiles');
        /* @var $renderBlock Aitoc_Aitdownloadablefiles_Block_FrontCatalogProductAitfiles */
        if ($renderBlock)
        {
            $linksHtml = $renderBlock->getCustomHtml();
        }
        
        return $linksHtml;
    }

} } 