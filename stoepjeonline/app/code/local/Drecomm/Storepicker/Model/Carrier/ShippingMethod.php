<?php

/**
* Our test shipping method module adapter
*/
class Drecomm_Storepicker_Model_Carrier_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
  /**
   * unique internal shipping method identifier
   *
   * @var string [a-z0-9_]
   */
  protected $_code = 'storepickershippingmethod';
  //protected $_isFixed = true;

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    // skip if not enabled
    if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
        return false;
    }

	$storeId = Mage::getSingleton('core/session')->getStorepickerId();
	$stores = Mage::getModel('storepicker/store');
	$stores->load($storeId);

	$storeName = $stores->getName();
    $storeStreet = $stores->getStreet();

	$canPickup = $stores->getPickup();
	$canSend = $stores->getSend();
	$sendcosts = $stores->getSendcosts();

	$now = Mage::getModel('core/date')->timestamp(time());

	if (date('G', $now) <= 10) {
		$time = $now + 86400;
	} else {
		$time = $now + 86400*2;
	}
	$daysOfWeekTranslated = array(
		"sunday" => "Zondag",
		"monday" => "Maandag",
		"tuesday" => "Dinsdag",
		"wednesday" => "Woensdag",
		"thursday" => "Donderdag",
		"friday" => "Vrijdag",
		"saturday" => "Zaterdag"
	);

	$send['sunday'] = $stores->getSendsunday();
	$send['monday'] = $stores->getSendmonday();
	$send['tuesday'] = $stores->getSendtuesday();
	$send['wednesday'] = $stores->getSendwednesday();
	$send['thursday'] = $stores->getSendthursday();
	$send['friday'] = $stores->getSendfriday();
	$send['saturday'] = $stores->getSendsaturday();


	for ($passedDays = 0; $passedDays < 7 && !$sendTime; $passedDays ++) {
		$timeCounter = $time + 86400*$passedDays;
		$dayOfWeek = strtolower(date('l', $timeCounter));
		if ($send[$dayOfWeek] != '-' && $send[$dayOfWeek] != '') {
			$sendTime = date('d-m-Y', $timeCounter) ." {$daysOfWeekTranslated[$dayOfWeek]} {$send[$dayOfWeek]}";
		}
	}


	$result = Mage::getModel('shipping/rate_result');

	//$carrierTitle = Mage::getStoreConfig('carriers/'.$this->_code.'/title');
	$carrierTitle = $storeName;
    $carrierTitle .= '&nbsp;' . $storeStreet;
	$methodTitle = Mage::getStoreConfig('carriers/'.$this->_code.'/name') .' ('. $sendTime .')';

	if ($canSend) {
		$method = Mage::getModel('shipping/rate_result_method');
		$method->setCarrier('storepickershippingmethod');
		$method->setCarrierTitle($carrierTitle);
		$method->setMethod('storepickershippingmethod');
		$method->setMethodTitle($methodTitle);
		$method->setPrice(5);
		$method->setCost(5);

		$result->append($method);
	}

	return $result;
  }

  public function getAllowedMethods()
  {
    return array('storepickershippingmethod' => $this->getConfigData('name'));
  }
}
