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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Standard Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MageWorx_MultiFees_Helper_Paypal_Data extends Mage_Paypal_Helper_Data
{
    public function prepareLineItems(Mage_Core_Model_Abstract $salesEntity, $discountTotalAsItem = true, $shippingTotalAsItem = false)
    {
        list($items, $totals, $discountAmount, $shippingAmount) = parent::prepareLineItems($salesEntity, $discountTotalAsItem, $shippingTotalAsItem);

        // multifees as line item

        if (Mage::helper('multifees')->isFeeEnabled()) {
            $items[] = new Varien_Object(array(
                'name'   => Mage::helper('multifees')->isIncludingTax() ? Mage::helper('multifees')->__('Additional Fees With Tax') : Mage::helper('multifees')->__('Additional Fees'),
                'qty'    => 1,
                'amount' => (float)round($salesEntity->getBaseMultifees(),2)
            ));
            $totals['tax'] += (float)(round($salesEntity->getMultifees(),2) - round($salesEntity->getBaseMultifees(),2)); //TODO getMultifeesTax()
        }
        
        return array($items, $totals, $discountAmount, $totals['shipping']);
    }

}
