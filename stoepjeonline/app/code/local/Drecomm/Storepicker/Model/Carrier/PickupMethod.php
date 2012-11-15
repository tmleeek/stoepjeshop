<?php

/**
* Our test shipping method module adapter
*/
class Drecomm_Storepicker_Model_Carrier_PickupMethod extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
  /**
   * unique internal shipping method identifier
   *
   * @var string [a-z0-9_]
   */
  protected $_code = 'storepickerpickupmethod';
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

	$open['sunday'] = $stores->getOpensunday();
	$open['monday'] = $stores->getOpenmonday();
	$open['tuesday'] = $stores->getOpentuesday();
	$open['wednesday'] = $stores->getOpenwednesday();
	$open['thursday'] = $stores->getOpenthursday();
	$open['friday'] = $stores->getOpenfriday();
	$open['saturday'] = $stores->getOpensaturday();

	for ($passedDays = 0; $passedDays < 7 && !$openTime; $passedDays ++) {
		$timeCounter = $time + 86400*$passedDays;
		$dayOfWeek = strtolower(date('l', $timeCounter));
		$dowString .= $dayOfWeek ."|";
		if ($open[$dayOfWeek] != '-' && $open[$dayOfWeek] != '') {
			$openTime = date('d-m-Y', $timeCounter) ." {$daysOfWeekTranslated[$dayOfWeek]} {$open[$dayOfWeek]}";
		}
	}

	$result = Mage::getModel('shipping/rate_result');

	//$carrierTitle = Mage::getStoreConfig('carriers/'.$this->_code.'/title');
	$carrierTitle = $storeName;
    $carrierTitle .= '&nbsp;' . $storeStreet;
	$methodTitle = Mage::getStoreConfig('carriers/'.$this->_code.'/name') .' ('. $openTime .')';

	if ($canPickup) {
		$method = Mage::getModel('shipping/rate_result_method');
		$method->setCarrier('storepickerpickupmethod');
		$method->setCarrierTitle($carrierTitle);
		$method->setMethod('storepickerpickupmethod');
		$method->setMethodTitle($methodTitle);
//		$method->setPrice(1);
//		$method->setCost(1);

		$result->append($method);
	}

	return $result;
  }

  public function getAllowedMethods()
  {
    return array('storepickerpickupmethod' => $this->getConfigData('name'));
  }
}
