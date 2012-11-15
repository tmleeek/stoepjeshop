<?php
class Ophirah_Qquoteadv_Block_Qquote extends  Mage_Checkout_Block_Cart_Abstract //Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	/**
	* Get customer session data
	* @return session data
	*/
	public function getCustomerSession()
	{
		return Mage::getSingleton('customer/session');
	}

	/**
	* Get product Information
	* @param integer $productId
	* @return session data
	*/
	public function getProduct($productId)
	{
		return Mage::getModel('catalog/product')->load($productId);
	}


	/**
	* Get delete url
	* @param object product information
	* @return string url
	*/
	public function getDeleteUrl($item)
	{
		$deleteUrl = $this->getUrl('qquoteadv/index/delete',
									array('id'=>$this->getProduct($item->getProductId())->getId())
									);
		return $deleteUrl;
	}

	/**
	* Delete items with attribute allowed_to_quotemode=false
	* @param object Ophirah_Qquoteadv_Model_Mysql4_qqadvproduct_Collection
	*/
	protected function _notAllowedDelete(Ophirah_Qquoteadv_Model_Mysql4_qqadvproduct_Collection $collection){
        if (count($collection)) {
            foreach ($collection as $item) {
                $productId = $item->getProductId();

                $_product = Mage::getModel('catalog/product')->load($productId);				
               
                if (!$_product->getData('allowed_to_quotemode')) { 
                  $collection->walk('delete');
                }
            }
        }
	}

	/**
	 * Get Product information from qquote_product table
	 * @return quote object
	 */
	public function getQuote()
    { 
    	$quoteId = $this->getCustomerSession()->getQuoteadvId();
		$collection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
						->addFieldToFilter('quote_id',$quoteId)
						//->load(true)
						;

		$this->_notAllowedDelete($collection);
		return $collection;
    }

	/**
	 * Get total number of items in quote
	 * @return integer total number of items
	 */
	public function getTotalQty()
    {
		$totalQty = 0;
		$products = $this->getQuote();
		foreach($products as $key => $value) {
			$totalQty += $value['qty'];
		}
		return $totalQty;
    }

	/**
	 * Get customer address
	 */
	public function getCustomer()
	{
		$id = $this->getCustomerSession()->getId();
		$cust = Mage::getModel('customer/customer')->load($id);
		return $cust;
	}

	/**
	 * Get attribute options array
	 * @param object $product
	 * @param string $attribute
	 * @return array
	 */
	public function getOption($product, $attribute)
	{
		$superAttribute = array();
        
		if($product->getTypeId() == 'simple') { 
			$superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, @unserialize($attribute));
		}
		return $superAttribute;
	}


	public function getValue($fieldname,$type)
	{
		if($value=$this->getRegisteredValue($type)){
			if($fieldname=="street1"){
				$street=$value->getData('street');
				if(is_array($street)){
					$street=explode("\n",$street);
					return $street[0];
				}
				else
				{
					return "";
				}
			}

			if($fieldname=="street2"){
				$street=$value->getData('street');

				if(is_array($street)){
					$street=explode("\n",$street);
					return $street[1];
				}
				else{
					return "";
				}
			}

			if($fieldname=="email"){
				return  Mage::getSingleton('customer/session')->getCustomer()->getEmail();
			}

			if($fieldname=="country"){
				//echo  Mage::getSingleton('customer/session')->getCustomer()->getCountryId();
				$countryCode = $value->getData("country_id");
				return $countryCode;

			}
			return $value->getData($fieldname);
		}
	}

	public function getRegisteredValue($type)
	{
		if($type == 'billing')
		{
			return Mage::getSingleton('customer/session')->getCustomer()->getPrimaryBillingAddress();
		}

		if($type == 'shipping')
		{
			return Mage::getSingleton('customer/session')->getCustomer()->getPrimaryShippingAddress();
		}
	}

	/**
	 * Get country collection
	 * @return array
	 */
	public function getCountryCollection()
	{
            $options = Mage::getResourceModel('directory/country_collection')->loadByStore()->load()->toOptionArray(false);           
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--'))); 
            return $options;            
	}

	/**
	 * Get region collection
	 * @param string $countryCode
	 * @return array
	 */
	public function getRegionCollection($countryCode)
	{
		$regionCollection = Mage::getModel('directory/region_api')->items($countryCode);
		return $regionCollection;
	}

	public function getCustomerEmail(){
	   return  Mage::getSingleton('customer/session')->getCustomer()->getEmail();
	}

    function getLoginUrl(){

        if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
             return $this->getUrl('customer/account/login/', array('referer'=>$this->getUrlEncoded('*/*/*',array('_current'=>true))));
        }

        return $this->getUrl('customer/account/login/');
    }

    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if (is_null($url)) {
            $url = Mage::getSingleton('checkout/session')->getContinueShoppingUrl(true);
            if (!$url) {
                $url = Mage::getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }
}