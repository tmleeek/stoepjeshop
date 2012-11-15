<?php
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
* @package     Mage_Rule
* @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Drecomm_Rulesfix_Model_Rule extends Mage_Rule_Model_Rule
{
    /**
    * Prepare data before saving
    *
    * @return Mage_Rule_Model_Rule
    */
    protected function _beforeSave()
    {
        // check if discount amount > 0
        //if ((int)$this->getDiscountAmount() < 0) {
        //    Mage::throwException(Mage::helper('rule')->__('Invalid discount amount.'));
        //}


        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }
        if ($this->getActions()) {
            $this->setActionsSerialized(serialize($this->getActions()->asArray()));
            $this->unsActions();
        }

        $this->_prepareWebsiteIds();

        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
        }
        Mage_Core_Model_Abstract::_beforeSave();
    }
}
