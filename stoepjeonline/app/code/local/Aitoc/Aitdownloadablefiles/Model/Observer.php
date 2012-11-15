<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Model/Observer.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ hIIjsSZomaTwZEIP('9bf05bce2793891aaa86ebc24300c890'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Model_Observer
{
    public function __construct()
    {
    }
    
    public function onModelSaveAfter($observer)
    {
    	$model = $observer->getObject();
    	
    	if ($model instanceof Mage_Catalog_Model_Product)
    	{
            $oReq = Mage::app()->getFrontController()->getRequest();
            
            if ($data = $oReq->getPost('aitdownloadablefiles')) 
            {
        	    $product = $model;
        	    
                if (isset($data['aitfile'])) 
                {
                    $_deleteItems = array();
                    foreach ($data['aitfile'] as $aitfileItem) 
                    {
                        if ($aitfileItem['is_delete'] == '1') 
                        {
                            if ($aitfileItem['aitfile_id']) 
                            {
                                $_deleteItems[] = $aitfileItem['aitfile_id'];
                            }
                        } 
                        else 
                        {
                            unset($aitfileItem['is_delete']);
                            
                            if (!$aitfileItem['aitfile_id']) 
                            {
                                unset($aitfileItem['aitfile_id']);
                            }
                            $aitfileModel = Mage::getModel('aitdownloadablefiles/aitfile');
                            $files = array();
                            if (isset($aitfileItem['file'])) {
                                $files = Zend_Json::decode($aitfileItem['file']);
                                unset($aitfileItem['file']);
                            }
                            
                            $aitfileModel->setData($aitfileItem)
                                ->setAitfileType($aitfileItem['type'])
                                ->setProductId($product->getId())
                                ->setStoreId($product->getStoreId());
    
                            // fix for version < 1.3
                            
                            $bIsNewVersion = true;
                            
                            $sVersion = Mage::getVersion();
                        
                            $aVersionParts = explode('.', $sVersion);
                            
                            if ($aVersionParts[0] < 1)
                            {
                                $bIsNewVersion = false;
                            }
                            else 
                            {
                                if ($aVersionParts[1] < 3)
                                {
                                    $bIsNewVersion = false;
                                }
                            }       
                                          
                            if ($bIsNewVersion)
                            {
                                $fileStatusNew = true;
                            }
                            else 
                            {
                                $fileStatusNew = false;
                                if (isset($files[0]) && $aitfileModel->getAitfileType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                                    $aitfileModel->setAitfileFile($files[0]['file']);
                                    if ($files[0]['status'] == 'new') {
                                        $fileStatusNew = true;
                                    }
                                }
                            }
                                
                                
                            if ($aitfileModel->getAitfileType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE AND $fileStatusNew) {
                                
                                if ($bIsNewVersion)
                                {
                                    $aFilesToMove = $files;
                                }
                                else 
                                {
                                    $aFilesToMove = $files[0]['file'];
                                }
                                
                                $aitfileFileName = Mage::helper('downloadable/file')->moveFileFromTmp(
                                    $aitfileModel->getBaseTmpPath(),
                                    $aitfileModel->getBasePath(),
                                    $aFilesToMove
                                );
                                
                                if ($bIsNewVersion)
                                {
                                    $aitfileModel->setAitfileFile($aitfileFileName);
                                }
                            }
    
                            $aitfileModel->save();
                        }
                    }
                    if ($_deleteItems) 
                    {
                        Mage::getResourceModel('aitdownloadablefiles/aitfile')->deleteItems($_deleteItems);
                    }
                }
            } 
    	}
    }
} } 