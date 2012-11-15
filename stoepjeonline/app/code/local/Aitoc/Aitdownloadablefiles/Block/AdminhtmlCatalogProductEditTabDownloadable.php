<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Block/AdminhtmlCatalogProductEditTabDownloadable.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ qccesYDiyZUhDkcE('4951b5aeb70b27172a67bd16e50b4e71'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Block_AdminhtmlCatalogProductEditTabDownloadable extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitdownloadablefiles/downloadable.phtml');
    }


    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('downloadable')->__('Useful Downloads');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('downloadable')->__('Useful Downloads');
    }

    /**
     * Check if tab can be displayed
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')
            ->setId('aitdownInfo');

        $accordion->addItem('aitfiles', array(
            'title'   => Mage::helper('adminhtml')->__('Useful Downloads'),
            'content' => $this->getLayout()->createBlock('aitdownloadablefiles/adminhtmlCatalogProductEditTabDownloadableFiles')->toHtml(),
            'open'    => true,
        ));
        
        $this->setChild('accordion', $accordion);

        return parent::_toHtml();
    }

} } 