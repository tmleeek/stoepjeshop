<?php

  class Drecomm_Storepicker_Model_Store extends Mage_Core_Model_Abstract
  {
      protected $_model = NULL;

      protected function _construct()
      {
          $this->_model = 'storepicker/store';
          $this->_init($this->_model);
      }

      /**
       * A simple delete all method.
       */
      public function deleteAll()
      {
          // note that the item couldn't be deleted.
          Mage::log(
            "Attempting to clear the cache",
            Zend_Log::INFO
            );

          $collection = Mage::getModel('storepicker/store')->getCollection();

          foreach ($collection as $storepickerItem) {
              try {
                  $storepickerItem->delete();
              } catch (Exception $e) {
                Mage::log(
                  sprintf("Couldn't delete record. [%s]", var_export($_item, TRUE)),
                  Zend_Log::ERR
                  );
              }
          }
      }

	  /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
		$this->_updateLonLat();
		return parent::_beforeSave();
    }

	/**
     * Update lon/lat if not set
     */
    protected function _updateLonLat() {

	/**
	 * compare data
	 */
	$origdata = $this->getOrigData();
	$data = $this->getData();

	if (
		// New store
		!$this->getId() ||

		// data changed
		$origdata['street']!=$data['street'] ||
		$origdata['housenr']!=$data['housenr'] ||
		$origdata['postal']!=$data['postal'] ||
		$origdata['city']!=$data['city'] ||
		$origdata['country']!=$data['country']
		) {

	    /**
	     * Get log/lat from google
	     */
	    $searchString = trim("{$data['street']}+{$data['housenr']}+{$data['postal']}+{$data['city']}+{$data['country']}", '+');
	    $searchString = str_replace(' ', '+', $searchString);
	    $geoCode = file_get_contents("http://maps.google.com/maps/api/geocode/json?address={$searchString}&sensor=false");
	    $output = json_decode($geoCode);
	    $latitude = $output->results[0]->geometry->location->lat;
	    $longitude = $output->results[0]->geometry->location->lng;

	    $this->setLongitude($longitude);
	    $this->setLatitude($latitude);
	}
    }
}
