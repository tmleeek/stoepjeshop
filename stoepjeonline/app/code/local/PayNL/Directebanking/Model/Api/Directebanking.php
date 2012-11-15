<?php
/**
 * Pay.nl iDEAL Api Model
 *
 * @category    PayNL
 * @package     PayNL_Directebanking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

// The first plugin that gets loaded, may load this...
// I am evil and so on
require_once(Mage::getBaseDir().DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR.
    "code".DIRECTORY_SEPARATOR."local".DIRECTORY_SEPARATOR."PayNL"
    .DIRECTORY_SEPARATOR."IXR_Library.inc.php");

class PayNL_Directebanking_Model_Api_Directebanking extends Varien_Object
{
    const     MIN_TRANS_AMOUNT = 50;   // 0.50 euro
    const     MAX_TRANS_AMOUNT = 100000; // 1000.00 euro
	/** NO PAYMENT_PROFILE_ID HERE, IT'S VARIABLE DUE TO MULTIPLE COUNTRIES!!!!!!!!!!! **/
    const     API_URL = "https://api.pay.nl:443/xmlrpc.php"; // xmlrpc service

    // Pay.nl variables (admin settable)
    protected $program_id      = null;
    protected $website_id      = null;
    protected $location_id     = null;

    // Transaction variables
    protected $country_id      = null;
    protected $consumer_ip     = null;
    protected $bank_id         = null;
    protected $amount          = 0;
    protected $description     = null;
    protected $testmode        = false;
    protected $transaction_id  = null;
    protected $paid_status     = false;
    protected $final	       = false;
    protected $consumer_info   = array();


    // URL locations
    protected $bank_url        = null;
    protected $return_url      = null;


    public function __construct ()
    {
        // TODO: for next version, do API calls to pay.nl to figure out
        // all the values...
        $this->program_id = $this->getConfigData('program_id');
        $this->website_id = $this->getConfigData('website_id');
        $this->location_id = $this->getConfigData('location_id');

        if ($this->getConfigData('test_flag') == 1) $this->testmode = true;

        if ($this->getConfigData('description'))
        {
            $this->description = $this->getConfigData('description');
        }
        else
        {
            $this->description = Mage::app()->getStore()->getName().' payment';
        }
    }
    
    /**
     * Getting config parametrs
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
             $value = Mage::getStoreConfig('payment/directebanking_directebanking/'.$key);
             if (is_null($value) || false===$value) {
                 $value = $default;
             }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    public function getBanks ()
    {
	$banks_array = array();

	$banks_array['568'] = Mage::helper('directebanking')->__("AT");
	$banks_array['559'] = Mage::helper('directebanking')->__("BE");
	$banks_array['571'] = Mage::helper('directebanking')->__("CH");
	$banks_array['562'] = Mage::helper('directebanking')->__("DE");
	$banks_array['565'] = Mage::helper('directebanking')->__("GB");
	$banks_array['556'] = Mage::helper('directebanking')->__("NL");
        
        return $banks_array;
    }

    public function createPayment ($bank_id, $amount, $return_url, $report_url, $emailAddress, $orderData)
    {

        // Generate all the needed stuff
        // We have no way to determine the country, so the id is NL
        $this->country_id = "NL";
        if (isset($_SERVER["REMOTE_ADDR"]))
        {
            $this->consumer_ip = $_SERVER["REMOTE_ADDR"];
        }
        else
        {
            $this->consumer_ip = ""; // Might be true for really bad webservers
        }

        if (  !$this->setAmount($amount) or
            !$this->setBankId($bank_id)
         )
        {
            // TODO: Show some kind of error notice
            echo $amount, $bank_id, $return_url, $report_url;
            return false;
        }

        $arguments = array();
        $arguments['programId'] = $this->getProgramId();
        $arguments['websiteId'] = $this->getWebsiteId();
        $arguments['locationId'] = $this->getLocationId();
        $arguments['orderAmount'] = $this->getAmount();
        $arguments['orderDesc'] = "Order ".$orderData['order']['increment_id'];
	$arguments['orderReturnUrl'] = $return_url;
        $arguments['orderExchangeUrl'] = $report_url;
	$arguments['consumerIp'] = $this->consumer_ip;
		$arguments['extra1'] = $orderData['order']['increment_id'];
        $arguments['profileId'] = $this->getBankId(); // Yeah, this is total ABUSE
	if ($this->testmode == true)
	{
		$arguments['testMode'] = true;
	}


        $result = $this->_doApiCall('transaction.submit',$arguments);
        if ($result != false && isset($result['result']) &&
          $result['result'] != "FALSE")
        {
            $this->session_id = $result['sessionId'];
            $this->transaction_id = $result['orderId'];
            $this->bank_url = $result['issuerUrl'];
        }
        return $this;
    }

    public function checkPayment ($transaction_id)
    {
        if (!$this->setTransactionId($transaction_id))
        {
            return false;
        }
        // Transaction_id == pay.nl orderId
        $arguments = array();
        $arguments['orderId'] = $transaction_id;
        $result = $this->_doApiCall('transaction.paymentStatus', $arguments);
        if ($result != false && isset($result['result']))
        {
		switch ($result['statusAction'])
		{
			case 'PAID': $this->paid_status = true; $this->amount=$result['orderAmount']; $this->final = true; break;
			case 'CANCEL': $this->paid_status = false; $this->amount=0; $this->final=true;  break;
			case 'PENDING': $this->paid_status = false; $this->amount=0; break;
			case 'PAID_CHECKAMOUNT': $this->paid_status = true; $this->amount=$result['orderAmount']; $this->final=true; break;
			case 'NOACTION':
			default:
				break;
		}

        }
        return $this;
    }
/*
  PROTECTED FUNCTIONS
*/

    protected function _doApiCall ($apicall, $arguments)
    {
        if (!isset($this->ixr))
        {
            $this->ixr = new IXR_clientSSL(self::API_URL);
        }

        try
        {
            if (!$this->ixr->query($apicall,$arguments))
            {
                throw new Exception('API error');
            }

            return $this->ixr->getResponse();
        }
        catch(Exception $ex)
        {
            return false;
        }
    }

/*
  SET AND GET FUNCTIONS
*/

    public function setProgramId ($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return ($this->program_id = $id);
    }
    public function getProgramId()
    {
        return $this->program_id;
    }
    public function setWebsiteId ($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return ($this->website_id = $id);
    }
    public function getWebsiteId()
    {
        return $this->website_id;
    }
    public function setLocationId ($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return ($this->location_id = $id);
    }
    public function getLocationId()
    {
        return $this->location_id;
    }
    public function setTestmode()
    {
        return ($this->testmode = true);
    }

    public function setBankId($bank_id)
    {
        if (!is_numeric($bank_id)) {
            return false;
        }
        return ($this->bank_id = $bank_id);
    }

    public function getBankId()
    {
        return $this->bank_id;
    }

    public function setAmount($amount)
    {
	// TODO: fix this
        /*if (!preg_match('^[0-9]{0,}$', $amount))
        {
            return false;
        }*/
        if (self::MIN_TRANS_AMOUNT > $amount)
        {
            return false;
        }
        if (self::MAX_TRANS_AMOUNT < $amount)
        {
            return false;
        }

        return ($this->amount = $amount);
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setDescription($description)
    {
        $description = substr($description, 0, 29);

        return ($this->description = $description);
    }

    public function getDescription()
    {
      return $this->description;
    }

    public function setReturnURL ($return_url) {
      if (!preg_match('|(\w+)://([^/:]+)(:\d+)?/(.*)|', $return_url)) {
        return false;
      }

      return ($this->return_url = $return_url);
    }

    public function getReturnURL () {
      return $this->return_url;
    }

    public function setReportURL ($report_url) {
      if (!preg_match('|(\w+)://([^/:]+)(:\d+)?/(.*)|', $report_url)) {
        return false;
      }

      return ($this->report_url = $report_url);
    }

    public function getReportURL () {
      return $this->report_url;
    }

    public function setTransactionId ($transaction_id) {
      if (empty($transaction_id)) {
        return false;
      }

      return ($this->transaction_id = $transaction_id);
    }

    public function getTransactionId () {
      return $this->transaction_id;
    }

    public function getBankURL () {
      return $this->bank_url;
    }

    public function getPaidStatus () {
      return $this->paid_status;
    }

    public function getConsumerInfo () {
      return $this->consumer_info;
    }
    public function getFinal()
    {
	return $this->final;
    }

}
?>
