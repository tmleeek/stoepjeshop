<?php
/**
 * Customer address api
 */
class Drecomm_Xmlrpc_Model_Customer_Address_Api extends Mage_Customer_Model_Address_Api
{
    protected $_mapAttributes = array(
        'customer_address_id' => 'entity_id'
    );

    public function __construct()
    {
        $this->_ignoredAttributeCodes[] = 'parent_id';
    }

    /**
     * Create new address for customer
     *
     * @param array $addressData
     * @return int
     */
    public function create($addressData, $redundantParam = null)
	{
        if (! (isset($addressData['website_id']) && isset($addressData['email'])) ){
            $this->_fault('not_enough_data_to_update', "Customer email and/or customer website_id is not set");
        }
        try{
			$customer = Mage::getModel('customer/customer')
				->setWebsiteId($addressData['website_id'])
				->loadByEmail($addressData['email']);
        } catch (Exception $e) {
            $this->_fault('not_exists', $e->getMessage());
        }
        if (!$customer->getId()) {
            $this->_fault('not_exists', "Customer does not exist");
		}
		$addressData['postcode'] = strtoupper($addressData['postcode']); // @TODO: Do we also need to remove all spaces?
		$this->lookupCountryID($addressData);

		try {
			if (isset($addressData['is_default_billing']) && $addressData['is_default_billing']) {
				$address = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
			} elseif( isset($addressData['is_default_shipping']) && $addressData['is_default_shipping']) {
				if ($customer->getDefaultShipping() == $customer->getDefaultBilling()) {
					$address = Mage::getModel('customer/address');
				} else {
					$address = Mage::getModel('customer/address')->load($customer->getDefaultShipping());
				}
			} else {
				$address = Mage::getModel('customer/address')
					->getCollection()
					->addAttributeToFilter('parent_id',   $customer->getId())
					->addAttributeToFilter('country_id',  $addressData['country_id'])
					->addAttributeToFilter('postcode',    $addressData['postcode'])
					->addAttributeToFilter('street',      $addressData['street'])
					->getFirstItem();
			}
		} catch(Exception $e) {
			// On error, create a new blank address
			$address = Mage::getModel('customer/address');
		}

		foreach ($this->getAllowedAttributes($address) as $attributeCode=>$attribute) {
            if (isset($addressData[$attributeCode])) {
                $address->setData($attributeCode, $addressData[$attributeCode]);
            }
        }

        if (isset($addressData['is_default_billing'])) {
            $address->setIsDefaultBilling($addressData['is_default_billing']);
        }

        if (isset($addressData['is_default_shipping'])) {
            $address->setIsDefaultShipping($addressData['is_default_shipping']);
        }
        $address->setCustomerId($customer->getId());

        $valid = $address->validate();
        if (is_array($valid)) {
            $this->_fault('data_invalid', implode("\n", $valid));
        }
        try {
            $address->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        $responseObject = new Zend_XmlRpc_Response($address->getId());
        return $responseObject;
	}

	private function lookupCountryID(&$addressData) {
		if (isset($addressData['country_id'])) {
			return;
		}
		if (strlen($country)==2) {
			$addressData['country_id'] = strtoupper($addressData['country']);
			unset($addressData['country']);
			return;
		}
		// @TODO: lookup country id in database
	}

}
