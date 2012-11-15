<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Model/Rewrite/FrontCatalogProduct.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ IRRasYkhBENpkORV('57545389711bd54375b683bf957a3837'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Model_Rewrite_FrontCatalogProduct extends Mage_Catalog_Model_Product
{
    
    public function duplicate()
    {
        $newProduct = parent::duplicate();
        
    	if ($this->getId() AND $newProduct->getId())
    	{
    	    $aitfileModel = Mage::getModel('aitdownloadablefiles/aitfile');
    	    
        	$collection = Mage::getResourceModel('aitdownloadablefiles/aitfile_collection');
        	    
            $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
    
            $select = $oDb->select()
                ->from(array('c' => $collection->getTable('aitfile')), '*')
                ->where('c.product_id=?', $this->getId())
            ;        
            
            $aItemList = $oDb->fetchAll($select);
            
            if ($aItemList)
            {
                foreach ($aItemList as $aItem)
                {
                    $aNewItem = $aItem;
                    
                    if ($aItem['aitfile_type'] == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) // need file copy
                    {
                        $sFilePath = $aitfileModel->getBasePath() . $aItem['aitfile_file'];
                        
                        if (file_exists($sFilePath))
                        {
                            $sSepPos = strrpos($sFilePath, '.');
                            
                            $sFileName = substr($sFilePath, 0, $sSepPos);
                            $sFileExt  = substr($sFilePath, $sSepPos);
                            
                            $sNewFilePath = $sFileName . $sFileExt;
                            
                            $index = 1;
                            
                            while (file_exists($sNewFilePath)) 
                            {
                            	$sNewFilePath = $sFileName . $index . $sFileExt;
                            	$index++;
                            }

                            copy($sFilePath, $sNewFilePath);
                            
                            $aPathParts = explode($aitfileModel->getBasePath(), $sNewFilePath);

                            $aNewItem['aitfile_file'] = $aPathParts[1];
                        }
                    }

                    unset($aNewItem['aitfile_id']);
                    
                    $aNewItem['product_id'] = $newProduct->getId();
                    
                    $iNewItemId = $oDb->insert($collection->getTable('aitfile'), $aNewItem);
                    
                    $iNewItemId = $oDb->lastInsertId();
                    
                    
                    $select = $oDb->select()
                        ->from(array('c' => $collection->getTable('aitfile_title')), '*')
                        ->where('c.aitfile_id=?', $aItem['aitfile_id'])
                    ;        
                    
                    $aTitleList = $oDb->fetchAll($select);
                    
                    if ($aTitleList)
                    {
                        foreach ($aTitleList as $aTitle)
                        {
                            $aNewTitle = $aTitle;
                            
                            unset($aNewTitle['title_id']);
                            
                            $aNewTitle['aitfile_id'] = $iNewItemId;
                            
                            $oDb->insert($collection->getTable('aitfile_title'), $aNewTitle);
                        }
                    }
                }
            }
    	}
    	    	
        return $newProduct;
    }    
    
} } 