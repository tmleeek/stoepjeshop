<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/controllers/DownloadController.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ ohhgsYehrjcpeahr('2196c47fc07c83890f1ce042554afa52'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_DownloadController extends Mage_Core_Controller_Front_Action
{

    /**
     * Return core session object
     *
     * @return Mage_Core_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Return customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _processDownload($resource, $resourceType)
    {
        $helper = Mage::helper('downloadable/download');
        /* @var $helper Mage_Downloadable_Helper_Download */

        $helper->setResource($resource, $resourceType);

        $fileName       = $helper->getFilename();
        $contentType    = $helper->getContentType();

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFilesize()) {
            $this->getResponse()
                ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename='.$fileName);
        }

        $this->getResponse()
            ->clearBody();
        $this->getResponse()
            ->sendHeaders();

        $helper->output();
    }

    public function aitfileAction()
    {
        $aitfileId = $this->getRequest()->getParam('aitfile_id', 0);
        $oAitfile = Mage::getModel('aitdownloadablefiles/aitfile')->load($aitfileId);
       
        if ($oAitfile->getId()) {
            $resource = '';
            $resourceType = '';
            if ($oAitfile->getAitfileType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                $resource = $oAitfile->getAitfileUrl();
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_URL;
            } elseif ($oAitfile->getAitfileType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                $resource = Mage::helper('downloadable/file')->getFilePath(
                    $oAitfile->getBasePath(), $oAitfile->getAitfileFile()
                );
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError(Mage::helper('downloadable')->__('Sorry, there was an error getting requested content. Please contact store owner.'));
            }
        }
        
        return $this->_redirectReferer();
    }

} } 