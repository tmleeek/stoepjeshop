<?php
/**
 * Class provides methods to add price rules for certain product or category
 * @author Sergey Gozhedrianov
 *
 */
class Drecomm_Xmlrpc_Model_Catalogrule_Api extends Mage_Api_Model_Resource_Abstract
{
   
    protected $_customerGroups = array();

	/**
	 * @param string $externalId ID of customer group
	 * @return Mage_Customer_Model_Group
	 */
    protected function _getCustomerGroupByExternalId($externalId) {
        $externalId = trim($externalId, '"');
        if (!array_key_exists($externalId, $this->_customerGroups)) {
            $this->_customerGroups[$externalId] = Mage::getModel('customer/group')->load($externalId, 'customer_group_code');
        }
        return $this->_customerGroups[$externalId];
    }

    /**
     * Convert rule type from AV to magento standards
     * @param string $ruleType
     */
    protected function _convertPriceRuleType($ruleType)
    {
        $magentoRule = '';
        switch ($ruleType) {
            case "percentage" : 
                $magentoRule = 'by_percent';
                break;
            case "discount_amount" :
                $magentoRule = "by_fixed";
                break;
            case "new_price" :
                $magentoRule = "to_fixed";
                break;
        }
        return $magentoRule;
    }
	
    /**
     * Convert dates in array from localized to internal format
     *
     * @param   array $array
     * @param   array $dateFields
     * @return  array
     */
    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }
    
    /**
     * 
     * @param array $dataArray
     * @param string $attributeName
     * @param string $attributeOperator
     * @param value $attributeValue
     */
    protected function _prepareConditionData($dataArray, $attributeName, $attributeOperator, $attributeValue)
    {
        $dataArray['rule']['conditions']['1--1']['attribute'] = $attributeName;
        $dataArray['rule']['conditions']['1--1']['operator'] = $attributeOperator;
        $dataArray['rule']['conditions']['1--1']['value'] = $attributeValue;
        unset($dataArray['sku']); // @TODO: Should this be $attributeName?
        $dataArray['rule']['conditions']['1--1']['type'] = 'catalogrule/rule_condition_product';
                
        $dataArray['rule']['conditions']['1']['type'] = 'catalogrule/rule_condition_combine';
        $dataArray['rule']['conditions']['1']['aggregator'] = 'all';
        $dataArray['rule']['conditions']['1']['value'] = 1;
        $dataArray['rule']['conditions']['1']['new_child'] = '';
        
        $dataArray['simple_action'] = $this->_convertPriceRuleType($dataArray['simple_action']);
        if (isset($dataArray['customer_group'])) {
            $customerGroups = explode(';', $dataArray['customer_group']);
            foreach($customerGroups as $groupName) {
                $groupModel = $this->_getCustomerGroupByExternalId($groupName);
                if (!$groupModel->getId()) {
                    $this->_fault('group_does_not_exist', 'Given group name doesn\'t exist');
                }
                $dataArray['customer_group_ids'][] = $groupModel->getId();
            }
        } else {
            $this->_fault('data_invalid', "Please specify group name");
        }
        
        
        unset($dataArray['customer_group']);
        
        if (isset($dataArray['website_ids'])) {
            if (!is_array($dataArray['website_ids'])) {
                $dataArray['website_ids'] = array($dataArray['website_ids']);
            }
        } else {
            $this->_fault('data_invalid', "Please specify website ID");
        }
        return $dataArray;
    }
    
    protected function _determineForeignRuleId($data) {
        if (!$data['foreign_rule_id']) {
            $data['foreign_rule_id'] = trim($data['sku']).'-'.trim($data['customer_group'],'" ');

            if ($data['from_date']) {
                // @TODO: use API to convert to internal date format
                list($d,$m,$y) = explode('-',$data['from_date']);
                $data['foreign_rule_id'] .= '-'.$y.$m.$d;
                unset($d, $m, $y);
            } else {
                $data['foreign_rule_id'] .= '-00000000';
            }
            // @TODO: Future usage: tier prices
            $data['foreign_rule_id'] .= '-1';
        }
        return $data['foreign_rule_id'];
    }
    
    /**
     * Create price rule for product
	 * @param array $data
	 * @return Zend_XmlRpc_Response
     */
    public function createProduct($data)
    {
        if (!$data['customer_group']) {
            // @TODO: determine what to do when no customer specified
        }
        
        $data['foreign_rule_id'] = $this->_determineForeignRuleId($data);
        
        $data = $this->_prepareConditionData($data, 'sku', '==', $data['sku']);
        $model = Mage::getModel('catalogrule/rule')->load($data['foreign_rule_id'], 'foreign_rule_id');
        $data = $this->_filterDates($data, array('from_date', 'to_date'));

        $validateResult = $model->validateData(new Varien_Object($data));
        if (is_array($validateResult)) {
            $this->_fault('data_invalid', implode("\n", $validateResult));
        }

        $data['conditions'] = $data['rule']['conditions'];
        unset($data['rule']);
        try {
			$model->loadPost($data);
            $model->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        $responseObject = new Zend_XmlRpc_Response($model->getId());
        return $responseObject; 
    }
    
    /**
     * Create price rule for category
     * @param array $data
     */
    public function createCategory($data)
    {
        if (!isset($data['product_group_av_id'])) {
            $this->_fault('data_invalid', 'Specify product group');
        }
        $categoryModel = Mage::getModel('catalog/category')->loadCategoryByAvId($data['product_group_av_id'],'product_group_av_id');
        if (!$categoryModel && !$categoryModel->getId()) {
            $this->_fault('category_not_exist');
        }
        $data = $this->_prepareConditionData($data, 'category_ids', '==', $categoryModel->getId());
        $model = Mage::getModel('catalogrule/rule');
        $data = $this->_filterDates($data, array('from_date', 'to_date'));
        $validateResult = $model->validateData(new Varien_Object($data));
        if (is_array($validateResult)) {
            $this->_fault('data_invalid', implode("\n", $validateResult));
        }
        $data['conditions'] = $data['rule']['conditions'];
        unset($data['rule']);

        try {
            $model->loadPost($data);
            $model->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        $responseObject = new Zend_XmlRpc_Response($model->getId());
        return $responseObject; 
    }
    
    /**
     * Delete price rule
     * @param array $ruleData
     */
    public function delete($ruleData)
    {
        if (!isset($ruleData['foreign_rule_id'])) {
            $this->_fault('data_invalid', "foreign_rule_id must be set");
        }
        try {
            $ruleToDelete = Mage::getModel('catalogrule/rule')->load($ruleData['foreign_rule_id'], 'foreign_rule_id');
            $ruleToDelete->delete();
        } catch(Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        
        $responseObject = new Zend_XmlRpc_Response("ok");
        return $responseObject;
	}

	/**
	 * Updates all price rules and rebuild price index by XmlRpc
	 *
	 * @param	array	$data	Dummy data from XmlRpc
	 * @return	Zend_XmlRpc_Response
	 */
	public function update_all_pricerules($data) {
		try {
            // From Mage::getResourceSingleton('catalogrule/observer')->applyAllRules($observer)

            /* @var Mage_CatalogRule_Model_Rule */
            $resource = Mage::getResourceSingleton('catalogrule/rule');
            $resource->applyAllRulesForDateRange($resource->formatDate(mktime(0,0,0)));
            Mage::app()->removeCache('catalog_rules_dirty');

        } catch(Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

		// Rebuild index
		try {
			$indexer = Mage::getSingleton('index/indexer');
			$process = $indexer->getProcessByCode('catalog_product_price');
			$process->reindexEverything();
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}

		$responseObject = new Zend_XmlRpc_Response('ok');
		return $responseObject;
	}
}
