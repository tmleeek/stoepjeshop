<?php

class TM_AjaxSearch_Model_Mysql4_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    public function getNewCollection($query)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this);
		$productstoshow = Mage::getStoreConfig('ajax_search/general/productstoshow');
		$attributesarr = array();
        $attributes = array('name');
        $searchattr = Mage::getStoreConfig('ajax_search/general/searchattr');
        if ($searchattr != '') {
            $attributes = explode(',', $searchattr);
        }
		$query_array= explode(' ', trim($query));

        $likeStmt = '';
        foreach ($query_array as $query_word){
            $likeStmt .= '#attr# LIKE %' . $query_word . '% AND ';
        }

        $likeStmt = substr($likeStmt, 0, -strlen(' AND '));
        $andWhere = array();
            foreach ($attributes as $attribute) {

                $this->addAttributeToSelect($attribute, true);
                    foreach ($query_array as $query_word) {
                        $andWhere[] = $this->_getAttributeConditionSql(
                            $attribute,
                            array('like' => '%' . $query_word . '%')
                        );
                    }
                $this->getSelect()->orWhere(implode(' AND ', $andWhere));
                $andWhere = array();
           }
		$this
            ->addAttributeToSort(Mage::getStoreConfig('ajax_search/general/sortby'), Mage::getStoreConfig('ajax_search/general/sortorder'))
            ->addUrlRewrite()
            ->setPageSize($productstoshow);

		$this->load();

        return $this;
    }

}