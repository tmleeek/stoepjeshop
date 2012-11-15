<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Basic
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Jcode_IdealBasic_Model_Basic
	extends Mage_Payment_Model_Method_Abstract
	{
		protected $_code = 'idealbasic';
		protected $_formBlockType = 'idealbasic/checkout_form';
		protected $_allowCurrencyCode = array('EUR');
		
	    protected $_isGateway               = false;
	    protected $_canAuthorize            = false;
	    protected $_canCapture              = true;
	    protected $_canCapturePartial       = false;
	    protected $_canRefund               = false;
	    protected $_canVoid                 = false;
	    protected $_canUseInternal          = false;
	    protected $_canUseCheckout          = true;
	    protected $_canUseForMultishipping  = false;

	    /**
	     * Validate currency availability for iDeal
	     */
	    public function validate()
	    {
	        parent::validate();
	        
	        $paymentInfo = $this->getInfoInstance();
	        ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) 
	        	? $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode() 
	        	: $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode() ;
	        
	        (!in_array($currency_code, $this->_allowCurrencyCode)) 
	        	? Mage::throwException(Mage::helper('idealbasic')->__('Selected currency code (%s) is not compatabile with iDeal', $currency_code))
	        	: FALSE ;
	        
	        return $this;
	    }	    
	    
	    /**
	     * Get URL to where the customer is send after placing the order
	     */
	    public function getOrderPlaceRedirectUrl()
	    {
	          return Mage::getUrl('ideal/order/redirect', array('_secure' => true));
	    }	    
	    
		/**
		* Return iDeal Baic API url
		*
		* @return string Payment API URL
		*/
	    public function getApiUrl()
	    {
	    	($this->getConfigData('run_mode') == 1)
	    		? $url = self::getTestUrl()
	    		: $url = self::getLiveUrl();

	        return $url;
	    }

		/**
		* Return iDeal Baic API test url
		*
		* @return string Payment API Test URL
		*/	    
	    public function getTestUrl()
	    {
	    	switch($this->getConfigData('bank_name'))
	    	{
	    		case 'ing' 	: 	$url = 'https://idealtest.secure-ing.com/ideal/mpiPayInitIng.do'; break;
	    		case 'abn'	:	$url = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp'; break;
	    		case 'rabo'	:	$url = 'https://idealtest.rabobank.nl/ideal/mpiPayInitRabo.do'; break;
	    	}
	    	
	    	return $url;
	    }
	    
		/**
		* Return iDeal Baic API live url
		*
		* @return string Payment API Live URL
		*/    
		public function getLiveUrl()
	    {
	    	switch($this->getConfigData('bank_name'))
	    	{
	    		case 'ing' 	: 	$url = 'https://ideal.secure-ing.com/ideal/mpiPayInitIng.do'; break;
	    		case 'abn'	:	$url = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp'; break;
	    		case 'rabo'	:	$url = 'https://ideal.rabobank.nl/ideal/mpiPayInitRabo.do'; break;
	    	}
	    	
	    	return $url;
	    }

		 /** Generates array of fields for redirect form
		*
		* @return array
		*/
	    public function getBasicCheckoutFormFields()
	    {
	        $order = $this->getInfoInstance()->getOrder();
	
	        $shippingAddress = $order->getShippingAddress();
	        $currency_code = $order->getBaseCurrencyCode();
	
	        if ($this->getConfigData('bank_name') == 'abn') {
	            $ammount = $order->getBaseGrandTotal();
				($this->getConfigData('run_mode') == 1)
					?	$merchant_key = 'TESTiDEALEASY'
					:	$merchant_key = $this->getConfigData('merchant_key');
	            $fields = array(
	                'pspid' => $merchant_key,    // Login name for the backend of ABN
	                'orderid' => $order->getIncrementId(),
	                'amount' => $ammount * 100,
	                'currency' => 'EUR',
	                'language' => 'NL_NL',
	                'title' => $description,
	                'accepturl' => Mage::getUrl('ideal/order/success', array('_secure' => true)),
	                'declineurl' => Mage::getUrl('ideal/order/failure', array('_secure' => true)),
	                'exceptionurl' => Mage::getUrl('ideal/order/failure', array('_secure' => true)),
	                'cancelurl' => Mage::getUrl('ideal/order/cancel', array('_secure' => true))
	            );
	        }else{
	            $fields = array(
	                'merchantID' => $this->getConfigData('merchant_id'),
	                'subID' => '0',
	                'amount' => $order->getBaseGrandTotal()*100,
	                'purchaseID' => $order->getIncrementId(),
	                'paymentType' => 'ideal',
	                'validUntil' => date('Y-m-d\TH:i:s.000\Z', strtotime ('+1 week')) // plus 1 week
	            );
	        }
	        
	        $i = 1;
	        $total = 0;
	        foreach ($order->getItemsCollection() as $item) :
	            $fields = array_merge($fields, array(
	                'itemNumber'.$i => $item->getSku(),
	                'itemDescription'.$i => $item->getName(),
	                'itemQuantity'.$i => $item->getQtyOrdered()*1,
	                'itemPrice'.$i => $item->getBasePrice()*100
	            ));
	            $i++;
	        $total += (($item->getQtyOrdered()*1) * $item->getBasePrice()*100);
	        endforeach;
	
	        if ($order->getBaseShippingAmount() > 0) {
	            $fields = array_merge($fields, array(
	                'itemNumber'.$i => $order->getShippingMethod(),
	                'itemDescription'.$i => $order->getShippingDescription(),
	                'itemQuantity'.$i => 1,
	                'itemPrice'.$i => $order->getBaseShippingAmount()*100
	            ));
	            $i++;
	            $total += ($order->getBaseShippingAmount()*100);
	        }
	
	        if ($order->getBaseTaxAmount() > 0) {
	            $fields = array_merge($fields, array(
	                'itemNumber'.$i => 'Tax',
	                'itemDescription'.$i => '',
	                'itemQuantity'.$i => 1,
	                'itemPrice'.$i => $order->getBaseTaxAmount()*100
	            ));
	            $i++;
	        }
	
	        if ($order->getBaseDiscountAmount() > 0) {
            $tax = $order->getBaseTaxAmount()*100;
            $diff = ($order->getBaseGrandTotal()*100) - ($total + $order->getBaseTaxAmount()*100);	        	
	            $fields = array_merge($fields, array(
	                'itemNumber'.$i => 'Discount',
	                'itemDescription'.$i => '',
	                'itemQuantity'.$i => 1,
	                'itemPrice'.$i => -$order->getBaseDiscountAmount()*100
	            ));
	            $i++;
	        }
	        
	
	        $fields = $this->appendHash($fields);
	
	        $description = $this->getConfigData('transaction_name');
	        ($description == '') 
	        	?	$description = Mage::app()->getStore()->getName().''.'payment'
	        	:	FALSE ;
	        	
	        $fields = array_merge($fields, array(
	            'language' => 'nl_NL',
	            'currency' => $currency_code,
	            'description' => $description,
	            'urlCancel' => Mage::getUrl('ideal/order/cancel', array('_secure' => true)),
	            'urlSuccess' => Mage::getUrl('ideal/order/success', array('_secure' => true)),
	            'urlError' => Mage::getUrl('ideal/order/failure', array('_secure' => true))
	        ));
	
	        $requestString = '';
	        $returnArray = array();
	
	        foreach ($fields as $k=>$v) :
	            $returnArray[$k] =  $v;
	            $requestString .= '&'.$k.'='.$v;
	        endforeach;
	
	        return $returnArray;
	    }
	    
		/**
		* Calculates and appends hash to form fields
		*
		* @param array $returnArray
		* @return array
		*/
	    public function appendHash($returnArray)
	    {
	        $merchantKey = $this->getConfigData('merchant_key');
	        $hashString = $merchantKey.implode('', $returnArray);
	        $hashString = str_replace(
	            array(' ', '\t', '\n', '&amp;', '&lt;', '&gt;', '&quote;'),
	            array('', '', '', '&', '<', '>', '\''),
	            $hashString);
	        $hash = sha1($hashString);
	        
	        return array_merge($returnArray, array('hash' => $hash));
	    }	    
	}