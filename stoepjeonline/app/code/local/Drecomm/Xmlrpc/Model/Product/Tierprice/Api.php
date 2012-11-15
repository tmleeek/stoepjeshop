<?php
/**
 * Create tier Price for product API
 * @author Sergey Gozhedrianov
 *
 */
class Drecomm_Xmlrpc_Model_Product_Tierprice_Api extends Mage_Catalog_Model_Product_Attribute_Tierprice_Api
{
	const ATTRIBUTE_CODE = 'tier_price';
	
    protected function _getProduct($productId, $store = null, $identifierType = null)
    {
        $loadByIdOnFalse = false;
        if ($identifierType === null) {
            $identifierType = 'sku';
            $loadByIdOnFalse = true;
        }
        $product = Mage::getModel('catalog/product');
        if ($store !== null) {
            $product->setStoreId($this->_getStoreId($store));
        }
        /* @var $product Mage_Catalog_Model_Product */
        if ($identifierType == 'sku') {
            $idBySku = $product->getIdBySku($productId);
            if ($idBySku) {
                $productId = $idBySku;
            }
            if ($idBySku || $loadByIdOnFalse) {
                $product->load($productId);
            }
        } elseif ($identifierType == 'id') {
            $product->load($productId);
        }
        return $product;
    }
	
    /**
     *  Set additional data before product saved
     *
     *  @param    Mage_Catalog_Model_Product $product
     *  @param    array $productData
     *  @return   object
     */
    protected function _prepareDataForSave ($product, $productData)
    {
        if (isset($productData['categories']) && is_array($productData['categories'])) {
            $product->setCategoryIds($productData['categories']);
        }

        if (isset($productData['websites']) && is_array($productData['websites'])) {
            foreach ($productData['websites'] as &$website) {
                if (is_string($website)) {
                    try {
                        $website = Mage::app()->getWebsite($website)->getId();
                    } catch (Exception $e) { }
                }
            }
            $product->setWebsiteIds($productData['websites']);
        }

        if (Mage::app()->isSingleStoreMode()) {
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        if (isset($productData['stock_data']) && is_array($productData['stock_data'])) {
            $product->setStockData($productData['stock_data']);
        }
    }
    
    /**
     *  Prepare tier prices for save
     *
     *  @param      Mage_Catalog_Model_Product $product
     *  @param      array $tierPrices
     *  @return     array
     */
    public function prepareTierPrices($product, $tierPrice = null)
    {
        if (!is_array($tierPrice)) {
            return null;
        }

        if (!is_array($tierPrice)) {
            $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid Tier Prices'));
        }

        $updateValue = array();

            if (!is_array($tierPrice)
                || !isset($tierPrice['qty'])
                || !isset($tierPrice['price'])) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid Tier Prices'));
            }

            if (!isset($tierPrice['website']) || $tierPrice['website'] == 'all') {
                $tierPrice['website'] = 0;
            } else {
                try {
                    $tierPrice['website'] = Mage::app()->getWebsite($tierPrice['website'])->getId();
                } catch (Mage_Core_Exception $e) {
                    $tierPrice['website'] = 0;
                }
            }

            if (intval($tierPrice['website']) > 0 && !in_array($tierPrice['website'], $product->getWebsiteIds())) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid tier prices. The product is not associated to the requested website.'));
            }

            if (!isset($tierPrice['customer_group_id'])) {
                $tierPrice['customer_group_id'] = 'all';
            }
            
            if ($tierPrice['customer_group_id'] == 'all') {
                $tierPrice['customer_group_id'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }

            $updateValue = array(
                'website_id' => $tierPrice['website'],
                'cust_group' => $tierPrice['customer_group_id'],
                'price_qty'  => $tierPrice['qty'],
                'price'      => $tierPrice['price']
            );
            
            if ($updateValue['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL) {
            	$updateValue['all_groups'] = 1;
            } 
            
            if (isset($tierPrice['delete']) && $tierPrice['delete'] == 1) {
            	$updateValue['delete'] = 1;
            }
        return $updateValue;
    }
    
    protected function _mergePrices($oldTier, $newTier) 
    {
    	foreach($oldTier as &$oldData) {
    		$result = array_intersect_assoc($oldData, $newTier);
    		if (      
    		          (
    		          array_key_exists('website_id', $result) && array_key_exists('all_groups', $result)
    		          ) 
    		      || (
    		          array_key_exists('website_id', $result) && array_key_exists('cust_group', $result)
    		          )
    		   ) 
    		   {
    		   	  $oldData['delete'] = 1;
    		   }
    	}
    	$oldTier[] = $newTier;
    	return $oldTier;
    }
	
	public function create($tierPriceData)
	{
        $tierPrice['website'] = $tierPriceData['website'];
        $tierPrice['customer_group_id'] = $tierPriceData['customer_group_id'];
        $tierPrice['qty'] = $tierPriceData['qty'];
        $tierPrice['price'] = $tierPriceData['price'];
        $tierPrice['delete'] = $tierPriceData['delete'];
        
	    $product = $this->_getProduct($tierPriceData['sku'], null, 'sku');
	    $result = $this->prepareTierPrices($product, $tierPrice);
	    $oldTier = $product->getData(self::ATTRIBUTE_CODE);
	    $mergedTierPrices = $this->_mergePrices($oldTier, $result);
	    $product->setData(self::ATTRIBUTE_CODE, $mergedTierPrices);
        try {
            if (is_array($errors = $product->validate())) {
                $strErrors = array();
                foreach($errors as $code=>$error) {
                    $strErrors[] = ($error === true)? Mage::helper('catalog')->__('Value for "%s" is invalid.', $code) : Mage::helper('catalog')->__('Value for "%s" is invalid: %s', $code, $error);
                }
                $this->_fault('data_invalid', implode("\n", $strErrors));
            }

            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }
        $response = new Zend_XmlRpc_Response($product->getId());
        return $response;
	}
}