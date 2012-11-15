<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Model/Aitfile.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ iqqMsSBqaeCoBZqa('e63f2485b1f4e2e3e290ac6909a70c67'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Model_Aitfile extends Mage_Core_Model_Abstract
{
    const XML_PATH_AITFILE_TITLE  = 'catalog/aitdownloadablefiles/aitfiles_title';
    const XML_PATH_AITFILE_TARGET = 'catalog/aitdownloadablefiles/aitfiles_target_new_window';

    /**
     * Enter description here...
     *
     */
    protected function _construct()
    {
        $this->_init('aitdownloadablefiles/aitfile');
        parent::_construct();
    }

    /**
     * Enter description here...
     *
     * @return Mage_Downloadable_Model_Sample
     */
    protected function _afterSave()
    {
        $this->getResource()->saveItemTitle($this);
        return parent::_afterSave();
    }

    public static function getBaseTmpPath()
    {
        return Mage::getBaseDir('media') . DS . 'downloadable' . DS . 'tmp' . DS . 'aitfiles';
    }

    public static function getBasePath()
    {
        return Mage::getBaseDir('media') . DS . 'downloadable' . DS . 'files' . DS . 'aitfiles';
    }

} } 