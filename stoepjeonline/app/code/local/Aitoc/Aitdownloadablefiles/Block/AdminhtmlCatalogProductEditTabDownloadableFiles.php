<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Block/AdminhtmlCatalogProductEditTabDownloadableFiles.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ IRRasYkhBENpkORV('fed9e01101bc9f446efc670136c22fbe'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Block_AdminhtmlCatalogProductEditTabDownloadableFiles extends Mage_Adminhtml_Block_Widget
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
#        $this->setTemplate('downloadable/product/edit/downloadable/aitfiles.phtml');
        $this->setTemplate('aitdownloadablefiles/files.phtml');
    }

    /**
     * Get model of the product that is being edited
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('downloadable')->__('Add New Row'),
                'id' => 'add_aitfile_item',
                'class' => 'add',
            ));
        return $addButton->toHtml();
    }

    public function getAitfileData()
    {
        $aitfilesArr = array();
#        $aitfiles = $this->getProduct()->getTypeInstance(true)->getAitfiles($this->getProduct());

        $collection = Mage::getResourceModel('aitdownloadablefiles/aitfile_collection')
        ->addProductToFilter($this->getProduct())
        ->addTitleToResult($this->getProduct()->getStoreId())
        ->load()
        ;


/*
		$oResource = Mage::getSingleton('core/resource');
		$sTable = $oResource->getTableName('aitoc_downloadable_file');        
        
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');

        $select = $oDb->select()
            ->from(array('f' => $sTable), '*')
#            ->joinInner(array('p' => $this->getTable('catalog/product')), 'o.product_id=p.entity_id', array())
            ->where('f.product_id=?', $this->getProduct()->getId())
        ;
*/        
        $oAitdownloadablefiles = Mage::getModel('aitdownloadablefiles/aitdownloadablefiles');
        $oAitfileModel = Mage::getModel('aitdownloadablefiles/aitfile');

#        $aItemList = $oDb->fetchAll($select);
        
        if ($collection)
        {
#            foreach ($aItemList as $aItem)
            foreach ($collection as $oAitfile)
            {
                $tmpAitfileItem = array(
                    'aitfile_id' => $oAitfile->getId(),
                    'title' => $oAitfile->getTitle(),
#                    'aitfile_url' => $oAitdownloadablefiles->getAitfileUrl($oAitfile->getId()),
                    'aitfile_url' => $oAitfile->getAitfileUrl(),
                    'aitfile_type' => $oAitfile->getAitfileType(),
                    'sort_order' => $oAitfile->getSortOrder(),
                );
                $file = Mage::helper('downloadable/file')->getFilePath(
                    $oAitfileModel->getBasePath(), $oAitfile->getAitfileFile()
                );
                if ($oAitfile->getAitfileFile() && is_file($file)) {
                    $tmpAitfileItem['file_save'] = array(
                        array(
                            'file' => $oAitfile->getAitfileFile(),
                            'name' => Mage::helper('downloadable/file')->getFileFromPathFile($oAitfile->getAitfileFile()),
                            'size' => filesize($file),
                            'status' => 'old'
                        ));
                }
                if ($this->getProduct() && $oAitfile->getStoreTitle()) {
                    $tmpAitfileItem['store_title'] = $oAitfile->getStoreTitle();
                }
                $aitfilesArr[] = new Varien_Object($tmpAitfileItem);
                
            }
        }
        
        /*
        foreach ($aitfiles as $item) {
            $tmpAitfileItem = array(
                'aitfile_id' => $item->getId(),
                'title' => $item->getTitle(),
                'aitfile_url' => $item->getAitfileUrl(),
                'aitfile_type' => $item->getAitfileType(),
                'sort_order' => $item->getSortOrder(),
            );
            $file = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Aitfile::getBasePath(), $aItem['aitfile_file']
            );
            if ($aItem['aitfile_file'] && is_file($file)) {
                $tmpAitfileItem['file_save'] = array(
                    array(
                        'file' => $aItem['aitfile_file'],
                        'name' => Mage::helper('downloadable/file')->getFileFromPathFile($aItem['aitfile_file']),
                        'size' => filesize($file),
                        'status' => 'old'
                    ));
            }
            if ($this->getProduct() && $item->getStoreTitle()) {
                $tmpAitfileItem['store_title'] = $item->getStoreTitle();
            }
            $aitfilesArr[] = new Varien_Object($tmpAitfileItem);
        }
        */

        return $aitfilesArr;
    }

    public function getUsedDefault()
    {
        return is_null($this->getProduct()->getAttributeDefaultValue('aitfiles_title'));
    }

    public function getAitfilesTitle()
    {
#        return Mage::getStoreConfig(Mage_Downloadable_Model_Aitfile::XML_PATH_SAMPLES_TITLE);
        return Mage::getStoreConfig(Aitoc_Aitdownloadablefiles_Model_Aitfile::XML_PATH_AITFILE_TITLE);
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'upload_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(array(
                    'id'      => '',
                    'label'   => Mage::helper('adminhtml')->__('Upload Files'),
                    'type'    => 'button',
                    'onclick' => 'Downloadable.massUploadByType(\'aitfiles\')'
                ))
        );
    }

    public function getUploadButtonHtml()
    {
        return $this->getChild('upload_button')->toHtml();
    }

    /**
     * Retrive config json
     *
     * @return string
     */
    public function getConfigJson()
    {
#        $this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('downloadable/file/upload', array('type' => 'aitfiles', '_secure' => true)));
        $this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('aitdownloadablefiles/file/upload', array('type' => 'aitfiles', '_secure' => true)));
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('aitfiles');
        $this->getConfig()->setFilters(array(
            'all'    => array(
                'label' => Mage::helper('adminhtml')->__('All Files'),
                'files' => array('*.*')
            )
        ));
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setWidth('32');
        $this->getConfig()->setHideUploadButton(true);
        return Zend_Json::encode($this->getConfig()->getData());
    }

    /**
     * Retrive config object
     *
     * @return Varien_Config
     */
    public function getConfig()
    {
        if(is_null($this->_config)) {
            $this->_config = new Varien_Object();
        }

        return $this->_config;
    }
} } 