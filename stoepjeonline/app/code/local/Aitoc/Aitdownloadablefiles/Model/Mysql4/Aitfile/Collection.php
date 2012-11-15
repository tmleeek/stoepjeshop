<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Model/Mysql4/Aitfile/Collection.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ cTTZsSrqgkQorWTO('409b5b9b08d76142fdc83753bf3a0d37'); ?><?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable aitfiles resource collection
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Aitoc_Aitdownloadablefiles_Model_Mysql4_Aitfile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Enter description here...
     *
     */
    protected function _construct()
    {
        $this->_init('aitdownloadablefiles/aitfile');
    }

    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Product|array|integer|null $product
     * @return Mage_Downloadable_Model_Mysql4_Aitfile_Collection
     */
    public function addProductToFilter($product)
    {
        if (empty($product)) {
            $this->addFieldToFilter('product_id', '');
        } elseif (is_array($product)) {
            $this->addFieldToFilter('product_id', array('in' => $product));
        } elseif ($product instanceof Mage_Catalog_Model_Product) {
            $this->addFieldToFilter('product_id', $product->getId());
        } else {
            $this->addFieldToFilter('product_id', $product);
        }

        return $this;
    }

    /**
     * Enter description here...
     *
     * @param integer $storeId
     * @return Mage_Downloadable_Model_Mysql4_Aitfile_Collection
     */
    public function addTitleToResult($storeId=0)
    {
        $this->getSelect()
            ->joinLeft(array('default_title_table' => $this->getTable('aitdownloadablefiles/aitfile_title')),
                '`default_title_table`.aitfile_id=`main_table`.aitfile_id AND `default_title_table`.store_id = 0',
                array('default_title' => 'title'))
            ->joinLeft(array('store_title_table' => $this->getTable('aitdownloadablefiles/aitfile_title')),
                '`store_title_table`.aitfile_id=`main_table`.aitfile_id AND `store_title_table`.store_id = ' . intval($storeId),
                array('store_title' => 'title','title' => new Zend_Db_Expr('IFNULL(`store_title_table`.title, `default_title_table`.title)')))
            ->order('main_table.sort_order ASC')
            ->order('title ASC');

        return $this;
    }

} } 