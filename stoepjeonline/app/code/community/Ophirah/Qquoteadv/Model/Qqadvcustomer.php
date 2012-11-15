<?php

class Ophirah_Qquoteadv_Model_Qqadvcustomer extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvcustomer');
    }
	
	/**
	 * Add quote to qquote_customer table
	 * @param array $params quote created information
	 * @return mixed
	 */
	public function addQuote($params)
	{
		$this->setData($params)
		      ->save()
		      ;		
		return $this;
	}
	
	/**
	 * Add customer address for the particular quote
	 * @param integer $id quote id to be updated
	 * @param array $params array of field(s) to be updated
	 * @return mixed
	 */
	public function addCustomer($id,$params)
	{ 
		$this->load($id)
		      ->addData($params)
		      ->setId($id)
		      ->save()
		      ;
		      
		return $this;		
	}
	
	public function updateQuote($id, $params)
	{
		$this->load($id)
		      ->setData($params)
		      ->setId($id)
		      ->save()
		      ;		
		return $this;
	}
	
	public function getStoreGroupName()
    {
        $storeId = $this->getStoreId(); 
        if (is_null($storeId)) {
            return $this->getStoreName(1); // 0 - website name, 1 - store group name, 2 - store name
        }
        return $this->getStore()->getGroup()->getName();
    }
    
     /**
     * Retrieve store model instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($storeId = $this->getStoreId()) {
            return Mage::app()->getStore($storeId);
        }
        return Mage::app()->getStore();
    }


     /**
     * Get formated quote created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAtStoreDate(), $format);
    }

    public function getBillingAddress(){

        //$name = $this->getCustomerName($this->getCustomerId());
        $address = $this->getAddress();
        
        $cityPostCode = $this->getCity();
        if(trim($this->getPostcode()))
           $cityPostCode.= ", ".$this->getPostcode();

        $country = Mage::app()->getHelper('qquoteadv')->getCountryName($this->getCountryId());
        $phone = $this->getTelephone();

        $str = "
            $address<br />
            $cityPostCode<br />
            $country<br />
            $phone<br />
        ";

       return $str; //$this->_formatAddress($str);
    } 
    
     public function getFullPath(){
	
		$valid = Mage::helper('qquoteadv')->isValidHttp($this->getPath()); 
		$path = urlencode($this->getPath());
		if($valid)
			return $path;
		else
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $path;  
     }
}