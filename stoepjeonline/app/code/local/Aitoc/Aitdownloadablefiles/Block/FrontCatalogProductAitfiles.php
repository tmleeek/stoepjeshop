<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Block/FrontCatalogProductAitfiles.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ ohhgsYehrjcpeahr('bacaa2520cd330e69e84278426d2dee5'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Block_FrontCatalogProductAitfiles extends Mage_Catalog_Block_Product_Abstract
{

    public function __construct()
    {
        parent::__construct();
        
        $this->setTemplate('aitdownloadablefiles/aitfiles.phtml');
    }
    
    public function getCustomHtml()
    {
        $sCustomHtml = '';
        
        $oAitfiles = $this->getAitfiles();
        
        if ($oAitfiles->count())
        {
             $sCustomHtml .= '
        <br>
        <dl class="item-options">
            <dt>' . $this->getAitfilesTitle() . '</dt>
            ';
            
            foreach ($oAitfiles as $oAitfile)
            {
                $sCustomHtml .= '
                <dd>
                    <a href="' . $this->getAitfileUrl($oAitfile) . '"';
                if ($this->getIsOpenInNewWindow())
                {
                    $sCustomHtml .= ' onclick="this.target=\'_blank\'"';
                }
                
                $sCustomHtml .= '>' . $oAitfile->getTitle() . '</a></dd>';
         
            }
            $sCustomHtml .= '</dl>';
        }        
        
        return $sCustomHtml;
    }    
    
    /**
     * Get downloadable product aitfiles
     *
     * @return array
     */
    public function getAitfiles()
    {
        
        $collection = Mage::getResourceModel('aitdownloadablefiles/aitfile_collection')
        ->addProductToFilter($this->getProduct())
        ->addTitleToResult($this->getProduct()->getStoreId())
        ->load()
        ;
        
        return $collection;
    }

    public function getAitfileUrl($aitfile)
    {
        return $this->getUrl('aitdownloadablefiles/download/aitfile', array('aitfile_id' => $aitfile->getId()));
    }

    /**
     * Return title of aitfiles section
     *
     * @return string
     */
    public function getAitfilesTitle()
    {
        if ($this->getProduct()->getAitfilesTitle()) {
            return $this->getProduct()->getAitfilesTitle();
        }
        return Mage::getStoreConfig(Aitoc_Aitdownloadablefiles_Model_Aitfile::XML_PATH_AITFILE_TITLE);
    }

    /**
     * Return true if target of link new window
     *
     * @return bool
     */
    public function getIsOpenInNewWindow()
    {
        return Mage::getStoreConfigFlag(Aitoc_Aitdownloadablefiles_Model_Aitfile::XML_PATH_AITFILE_TARGET);
    }
} } 