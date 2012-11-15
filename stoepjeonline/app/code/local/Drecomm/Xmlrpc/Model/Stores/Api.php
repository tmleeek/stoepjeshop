<?php

/**
 * Class provides methods to add stores
 * @author Richard Jansen
 *
 */
class Drecomm_Xmlrpc_Model_Stores_Api extends Mage_Api_Model_Resource_Abstract {

    /**
     * Field name mapping
     * @var array
     */
    protected $mapping = array(
	'Customer_No'           => 'customerno',
	'Name'                  => 'name',
	'Street'                => 'street',
	'Postal'                => 'postal',
	'City'                  => 'city',
	'Country'               => 'country',
	'Phone'                 => 'phone',
	'E_mail'                => 'email',
	'Banknr'                => 'banknr',
	'Pickup'                => 'pickup',
	'Send'                  => 'send',
	'ID'                    => 'importid',
	'Delivery_Area__km_'    => 'sendradius',
	''
    );

    /**
     * Market day mapping
     * @var array
     */
    protected $market_days_mapping = array(
	'7' => 'sunday',
	'1' => 'monday',
	'2' => 'tuesday',
	'3' => 'wednesday',
	'4' => 'thursday',
	'5' => 'friday',
	'6' => 'saturday'
    );

    /**
     * opening times prefix
     * @var string
     */
    protected $opening_times_prefix = 'open';

    /**
     * delivery times prefix
     * @var string
     */
    protected $delivery_times_prefix = 'send';

    /**
     * Create or update store entry in database
     * @param array $dataIn
     * @return Zend_XmlRpc_Response
     */
    public function create($dataIn) {

	// check input
	if ($dataIn['Street'] === '') {
	    $this->_fault('data_invalid', 'Street is empty');
	}

	// Get model
	$model = Mage::getModel('storepicker/store')->load($dataIn['ID'], 'importid');

	$data = array();

	// Add default values for opening days
	foreach (array_values($this->market_days_mapping) as $column) {
	    $data[$this->opening_times_prefix . $column] = '-';
	    $data[$this->delivery_times_prefix . $column] = '-';
	}

	// Fill data
	foreach ($this->mapping as $rpcField => $dbField) {
	    $data[$dbField] = $dataIn[$rpcField];
	}

	// Determine market day
	if (!array_key_exists($dataIn['Market_day'], $this->market_days_mapping)) {
	    $this->_fault('data_invalid', 'Unknown market day');
	}
	$day = $this->market_days_mapping[$dataIn['Market_day']];

	// Set opening times
	$data[$this->opening_times_prefix . $day] = $this->_extractTimes($dataIn['Starting_Time_Market'], $dataIn['Ending_Time_Market']);

	// Set delivery times
	$data[$this->delivery_times_prefix . $day] = $this->_extractTimes($dataIn['Delivery_Time_From'], $dataIn['Delivery_Time_To']);

	// Fill model
	$id = null;
	if ($model->isObjectNew()) {
	    $model->setData($data);
	} else {
	    $id = $model->getId();
	    $model->addData($data);
	}

	// Save it
	try {
	    if ($model->isObjectNew()) {
		$id = $model->save();
	    } else {
		$model->save();
	    }
	} catch (Exception $e) {
	    $this->_fault('data_invalid', $e->getMessage());
	}

	$overig = array(
	    'textualpos',
	    'sendcosts',
	);

	// Return response
	$responseObject = new Zend_XmlRpc_Response($id);

	return $responseObject;
    }

    /**
     * Create opening times string
     * @param string $startTime eq. 1754-01-01 08:30:00.0
     * @param string $endTime eq.1754-01-01 12:30:30.0
     * @return string
     */
    protected function _extractTimes($startTimestamp, $endTimestamp) {
	list(, $startTime) = explode(' ', $startTimestamp);
	list(, $endTime) = explode(' ', $endTimestamp);

	return $this->_parseTime($startTime) . ' - ' . $this->_parseTime($endTime);
    }

    /**
     * HH:MM:SS.MS => HH:MM
     * @param string $time
     * @return string
     */
    protected function _parseTime($time) {
	if (preg_match('/^(\d+:\d+)/', $time, $m)) {
	    return $m[1];
	}
	return '0:00';
    }

    /**
     * Delete store from shop
     * @param array $dataIn
     */
    public function delete ($dataIn) {

	/**
	 * Load model
	 */
	$model = Mage::getModel('storepicker/store')->load($dataIn['ID'], 'importid');

	/**
	 * trhow exception when trying to delete a non existing store
	 */
	if ($model->isObjectNew()) {
	    $this->_fault('Cannot delete new store!');
	}

	// Only existing can be deleted
	try {
	    $model->delete();
	} catch (Exception $e) {
	    $this->_fault($e->getCode(),$e->getMessage());
	}

	return new Zend_XmlRpc_Response('succesfully deleted');
    }

}
