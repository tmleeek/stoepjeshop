<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_MultiFees_Model_Paypal_Standard extends Mage_Paypal_Model_Standard
{
    /**
     * Return form field array
     *
     * @return array
     */
    public function getStandardCheckoutFormFields()
    {
        if ($this->getQuote()->getIsVirtual()) {
            $address = $this->getQuote()->getBillingAddress();
        } else {
            $address = $this->getQuote()->getShippingAddress();
        }

        $rArr = parent::getStandardCheckoutFormFields();
		$orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		
        $nLasItem = 0;
        $bIsCartCmd = false;
        foreach ($rArr as $k => $v) {
            if ($k == 'cmd' && $v == '_cart')
                $bIsCartCmd = true;
            if (preg_match('/item_name_(\d+)/', $k, $mathes)) {
                $nLasItem = max($mathes[1], $nLasItem);
            }
        }
        if ($bIsCartCmd && $nLasItem > 0) {
            $nLasItem++;
            $rArr = array_merge($rArr, array(
                'item_name_'.$nLasItem   => Mage::helper('multifees')->isIncludingTax() ? Mage::helper('multifees')->__('Additional Fees With Tax') : Mage::helper('multifees')->__('Additional Fees'),
                'item_number_'.$nLasItem => Mage::helper('multifees')->__('Multifees'),
                'quantity_'.$nLasItem    => 1,
                'amount_'.$nLasItem      => sprintf('%.2f', (float)$order->getBaseMultifees()),
            ));
        } else {
            $rArr['amount'] = (float)$rArr['amount'] + (float)$order->getBaseMultifees();
            $rArr['amount'] = sprintf('%.2f', $rArr['amount']);
        }
		
        return $rArr;
    }
}
