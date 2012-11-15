<?php
/**
 * 
 * @author Sergey Gozhedrianov
 *
 */
class Drecomm_Xmlrpc_Model_Customer_Api extends Mage_Customer_Model_Customer_Api
{
    /**
     * Retrieve cutomers data
     *
     * @param  array $filters
     * @return array
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('*');

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_mapAttributes[$field])) {
                        $field = $this->_mapAttributes[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();
        foreach ($collection as $customer) {
            $data = $customer->toArray();
            $row  = array();

            foreach ($this->_mapAttributes as $attributeAlias => $attributeCode) {
                $row[$attributeAlias] = (isset($data[$attributeCode]) ? $data[$attributeCode] : null);
            }

            foreach ($this->getAllowedAttributes($customer) as $attributeCode => $attribute) {
                if (isset($data[$attributeCode])) {
                    $row[$attributeCode] = $data[$attributeCode];
                }
            }

            $result[] = $row;
        }

        return $result;
    }
    /**
     * Create new customer
     *
     * @param array $customerData
     * @return int
     */
    public function create($customerData)
    {
        if (isset($customerData['group'])) {
            $groupModel = Mage::getModel('customer/group')->load($customerData['group'], 'customer_group_code');
            if(!$groupModel->getId()){
                try {
                    $groupModel->setCode($customerData['group'])
                                 ->setTaxClassId(3)
                                 ->save();
                } catch (Mage_Core_Exception $e) {
                    $this->_fault('data_invalid', $e->getMessage());
                }
            }
			$groupModel->setData('customer_group_av_id', $customerData['av_group_id']);
			$groupModel->save();
            $customerData['group_id'] = $groupModel->getId();
            
        }
        if (isset($customerData['website_id']) && isset($customerData['email'])) {
            $customer = Mage::getModel('customer/customer')->setWebsiteId($customerData['website_id'])
                                                                ->loadByEmail($customerData['email']);
        }
        if (!$customer->getId()) {
            $customer = Mage::getModel('customer/customer');
        }
		// set password
		if (array_key_exists('password', $customerData)) {
			$customer->setPassword($customerData['password']);
			unset($customerData['password']);
		}

        try {
            foreach ($this->getAllowedAttributes($customer) as $attributeCode=>$attribute) {
                if (isset($customerData[$attributeCode])) {
                    $customer->setData($attributeCode, $customerData[$attributeCode]);
                }
            }

          $customer->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        $responseObject = new Zend_XmlRpc_Response($customer->getId());
        return $responseObject;
    }
    
    /**
     * Update customer data
     *
     * @param int $customerId
     * @param array $customerData
     * @return boolean
     */
    public function update($customerData, $redundant = null)
    {
        if (! (isset($customerData['website_id']) && isset($customerData['email'])) ){
            $this->_fault('not_enough_data_to_update', "Customer email and customer website_id is not set");
        }
        try{
            $customer = Mage::getModel('customer/customer')->setWebsiteId($customerData['website_id'])
                                                            ->loadByEmail($customerData['email']);
        } catch (Exception $e) {
            $this->_fault('not_exists', $e->getMessage());
        }

        if (!$customer->getId()) {
            $this->_fault('not_exists');
        }

        if (isset($customerData['group'])) {
                $groupModel = Mage::getModel('customer/group')->load($customerData['group'], 'customer_group_code');
                if(!$groupModel->getId()){
                    try {
                        $groupModel->setCode($customerData['group'])
                                     ->setTaxClassId(3)
									 ->setCustomerGroupAvId(15)
                                     ->save();
                    } catch (Mage_Core_Exception $e) {
                        $this->_fault('data_invalid', $e->getMessage());
                    }
                }
                $customerData['group_id'] = $groupModel->getId();
                
            }
        foreach ($this->getAllowedAttributes($customer) as $attributeCode=>$attribute) {
            if (isset($customerData[$attributeCode])) {
                $customer->setData($attributeCode, $customerData[$attributeCode]);
            }
        }

        $customer->save();
        $response = new Zend_XmlRpc_Response("ok");
        return $response;
    }
    
    /**
     * Delete customer
     *
     * @param int $customerId
     * @return boolean
     */
    public function delete($customerData)
    {
        if (! (isset($customerData['website_id']) && isset($customerData['email'])) ){
            $this->_fault('not_enough_data_to_update', "Customer email and customer website_id is not set");
        }
        try{
            $customer = Mage::getModel('customer/customer')->setWebsiteId($customerData['website_id'])
                                                            ->loadByEmail($customerData['email']);
        } catch (Exception $e) {
            $this->_fault('not_exists', $e->getMessage());
        }

        if (!$customer->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $customer->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
}
