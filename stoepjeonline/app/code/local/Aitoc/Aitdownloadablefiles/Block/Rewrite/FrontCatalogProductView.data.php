<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Block/Rewrite/FrontCatalogProductView.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ cTTZsSrqgkQorWTO('05450e0016b850959383902e42ffec06'); ?><?php
/**
 * @copyright  Copyright (c) 2010 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Block_Rewrite_FrontCatalogProductView extends Mage_Catalog_Block_Product_View
{
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