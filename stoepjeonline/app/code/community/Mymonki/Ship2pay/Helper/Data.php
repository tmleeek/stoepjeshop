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
 * @category   Mymonki
 * @package    Mymonki_Ship2pay
 * @copyright  Copyright (c) 2010 Freshmind Sp. z o.o. (pawel@freshmind.pl)
 */

class Mymonki_Ship2pay_Helper_Data extends Mage_Core_Helper_Abstract {

	const XML_PATH_PAYMENT_METHODS = 'payment';
	
	public function getPaymentMethodOptions($storeId = null)
	{
		
		$methods = Mage::helper('payment')->getPaymentMethods($storeId);
		$options = array();
		foreach ($methods as $code => $methodConfig)
		{
			
			$prefix = self::XML_PATH_PAYMENT_METHODS . '/' . $code . '/';
            if (!$model = Mage::getStoreConfig($prefix . 'model', $storeId)) {
                continue;
            }
            $methodInstance = Mage::getModel($model);
            if (!$methodInstance) {
                continue;
            }
           
            $method_label = $methodInstance->getConfigData('title');
            $method_label = trim($method_label);
            
            if(!$method_label)
            	$method_label = $methodInstance->getCode();
            
            if($methodInstance->getCode() == 'klarna_partpayment')
            	Mage::log(var_export($methodInstance->getConfigData('title'), true));
            	
			array_unshift($options, array(
				'value' => $methodInstance->getCode(),
				'label' => $method_label,
			));
		}
		return $options;
	}
	
	public function getShipingMethodOptions($storeId = null)
	{
		
		$carriers = Mage::getSingleton('shipping/config')->getAllCarriers($storeId);
		$options = array();
		
		foreach($carriers as $carrierCode=>$carrierConfig)
		{
			array_unshift($options, array(
				'value' => $carrierCode,
				'label' => $this->getCarrierName($carrierCode)
			));
		}
		
		return $options;
	}
	
	public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }
	
}
?>
