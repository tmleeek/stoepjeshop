<?php

class Ophirah_Qquoteadv_Model_Requestitem extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/requestitem');
    }
	
	
	/**
	 * Add item to request for the particular quote
	 * @param array $params array of field(s) to be inserted
	 * @return mixed
	 */
	public function addItem($params)
	{ 		
		$this->setData($params)
		      ->save()
		      ;	
		return $this;		
	}
	
	/**
	 * Add items to request for the particular quote
	 * @param array $params array of field(s) to be inserted
	 * @return mixed
	 */
	public function addItems($params){
	    
	    foreach($params as $key=>$values)
	       if(!$this->_isDublicatedData($values))
	           $this->addItem($values);
	    
	    return $this;
	}
	
	/**
	 * Checking item / qty for blocking dublication request
	 * @param array $params array of field(s) should to be inserted
	 * @return mixed
	 */
	protected function _isDublicatedData($params){
	      $quoteId       = $params['quote_id'];
	      $productId     = $params['product_id'];
          $qtyRequest    = $params['request_qty'];
          
	      $collection =  Mage::getModel('qquoteadv/requestitem')->getCollection()
                	               ->addFieldToFilter('quote_id', $quoteId)
                	               ->addFieldToFilter('product_id', $productId)
                	               ->addFieldToFilter('request_qty', $qtyRequest)
                	               //->load(true)
                	               ;
                	               
        return (count($collection)  > 0)?true:false; 
	}
}