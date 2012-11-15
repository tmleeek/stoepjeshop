<?php
/**
 * 
 * @author Sergey Gozhedrianov
 *
 */
class Drecomm_Xmlrpc_Model_Category_Category extends Mage_Catalog_Model_Category
{
    /**
     * Function allows loading categories by unique field
     * @param string $avId
     * @param string $fieldName
     */
    public function loadCategoryByAvId($avId, $fieldName)
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter($fieldName, $avId)
                            ->setPage(1,1)
                            ->load();
        if (sizeof($collection)) {
            foreach($collection as $category) {
                $categoryId =  $category->getId();
                return Mage::getModel('catalog/category')->load($categoryId);         
            }
        }
        return null;        
    }
}