<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Advanced
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Jcode_IdealAdvanced_Model_Api_Advanced 
	extends Varien_Object
	{
	    const STATUS_OPEN = 'Open';
	    const STATUS_EXPIRED = 'Expired';
	    const STATUS_SUCCESS = 'Success';
	    const STATUS_CANCELLED = 'Cancelled';
	    const STATUS_FAILED = 'Failed';

	    protected $_security;

	    protected $_conf;
	    
	    protected $_http;
	
		public function __construct()
	    {
	        $this->_http = new Varien_Http_Adapter_Curl();
	        $this->_security = new Jcode_IdealAdvanced_Model_Api_Advanced_Security();
	
	        if ($this->getConfigData('run_mode') == 1) {
	            switch($this->getConfigData('bank_name'))
	            {
	            	case 'ing':	$acquirerUrl = 'https://idealtest.secure-ing.com/ideal/iDeal';	break;
	            	case 'abn': $acquirerUrl = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp'; break;	
	            	case 'rabo':	$acquirerUrl = 'https://idealtest.rabobank.nl/ideal/iDeal'; break;
	            	case 'simulator': $acquirerUrl = 'https://www.ideal-simulator.nl/professional/';
	            }
	        } else {
	        	switch($this->getConfigData('bank_name'))
	            {
		    		case 'ing' 	: 	$acquirerUrl = 'https://ideal.secure-ing.com/ideal/iDeal'; break;
		    		case 'abn'	:	$acquirerUrl = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp'; break;
		    		case 'rabo'	:	$acquirerUrl = 'https://ideal.rabobank.nl/ideal/iDeal';
	            }
	        }
	
	        if (!($description = $this->getConfigData('description'))) {
	            $description = Mage::app()->getStore()->getName() . ' payment';
	        }
      
	
	        $this->_conf = array(
	            'PRIVATEKEY' => $this->getConfigData('private_key'),
	            'PRIVATEKEYPASS' => $this->getConfigData('private_key_password'),
	            'PRIVATECERT' => $this->getConfigData('private_certificate'),
	            'AUTHENTICATIONTYPE' => 'SHA1_RSA',
	            'CERTIFICATE0' => $this->getConfigData('ideal_certificate'),
	            'ACQUIRERURL' => $acquirerUrl,
	            'ACQUIRERTIMEOUT' => '10',
	            'MERCHANTID' => $this->getConfigData('merchant_id'),
	            'SUBID' => '0',
	            'MERCHANTRETURNURL' => Mage::getUrl('ideal/order/result', array('_secure' => true)),
	            'CURRENCY' => 'EUR',
	            'EXPIRATIONPERIOD' => 'PT10M',
	            'LANGUAGE' => 'nl',
	            'DESCRIPTION' => $description,
	            'ENTRANCECODE' => ''
	        );

	    	if ((int)$this->getConfigData('expiration') >= 1 && $this->getConfigData('expiration') < 60) {
	            $this->_conf['EXPIRATIONPERIOD'] = 'PT' . $this->getConfigData('expiration') . 'M';
	        } else if ($this->getConfigData('description') == 60) {
	            $this->_conf['EXPIRATIONPERIOD'] = 'PT1H';
	        }
	    }
		
	    public function getConfigData($key, $default=false)
	    {
	        if (!$this->hasData($key)) {
	             $value = Mage::getStoreConfig('payment/idealadvanced/'.$key);
	             if (is_null($value) || false===$value) {
	                 $value = $default;
	             }
	            $this->setData($key, $value);
	        }
	        return $this->getData($key);
	    }
	
	    public function processRequest($requestType, $debug = false)
	    {
	        if($requestType instanceof Jcode_IdealAdvanced_Model_Api_Advanced_DirectoryRequest) {
	            $response = $this->processDirRequest($requestType);
	        } else if($requestType instanceof Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerStatusRequest) {
	            $response = $this->processStatusRequest($requestType);
	        } else if($requestType instanceof Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerTrxRequest) {
	            $response = $this->processTrxRequest($requestType);
	        }

	        if ($debug) {
	
	            if ($response === false) {
	                $responseData = $this->getError();
	            } else {
	                $responseData = $response->getData();
	            }
				
	            Mage::getModel('idealadvanced/api_debug')
	                ->setResponseBody(get_class($requestType) . "\n" . print_r($responseData, true))
	                ->setRequestBody(get_class($requestType) . "\n" . print_r($requestType->getData(), true))
	                ->save();

	        }
			
	        return $response;
	    }

	    public function processDirRequest($request)
	    {
	        if ($request->getMerchantId() == "") {
	            $request->setMerchantId($this->_conf["MERCHANTID"]);
	        }
	
	        if ($request->getSubId() == "") {
	            $request->setSubId($this->_conf["SUBID"]);
	        }
	
	        if ($request->getAuthentication() == "") {
	            $request->setAuthentication($this->_conf["AUTHENTICATIONTYPE"]);
	        }
	
	        $res = new Jcode_IdealAdvanced_Model_Api_Advanced_DirectoryResponse();
	
	        if (!$request->checkMandatory()) {
	            $res->setError(Mage::helper('idealadvanced')->__('Required fields missing'));

	            return $res;
	        }
	
	        $timestamp = $this->getGMTTimeMark();
	        $token = "";
	        $tokenCode = "";
	
	        if ("SHA1_RSA" == $request->getAuthentication()) {
	            $message = $timestamp . $request->getMerchantId() . $request->getSubId();
	            $message = $this->_strip($message);
	
	            $token = $this->_security->createCertFingerprint($this->_conf["PRIVATECERT"]);
	
	            $tokenCode = $this->_security->signMessage($this->_conf["PRIVATEKEY"], $this->_conf["PRIVATEKEYPASS"], $message);
	
	            $tokenCode = base64_encode($tokenCode);
	        }
	
	        $reqMsg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
	            . "<DirectoryReq xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">\n"
	            . "<createDateTimeStamp>" . utf8_encode( $timestamp ) . "</createDateTimeStamp>\n"
	            . "<Merchant>\n"
	            . "<merchantID>" . utf8_encode( htmlspecialchars( $request->getMerchantId() ) ) . "</merchantID>\n"
	            . "<subID>" . utf8_encode( $request->getSubId() ) . "</subID>\n"
	            . "<authentication>" . utf8_encode( $request->getAuthentication() ) . "</authentication>\n"
	            . "<token>" . utf8_encode( $token ) . "</token>\n"
	            . "<tokenCode>" . utf8_encode( $tokenCode ) . "</tokenCode>\n"
	            . "</Merchant>\n"
	            . "</DirectoryReq>";
	
	        $response = $this->_post($this->_conf["ACQUIRERURL"], $this->_conf["ACQUIRERTIMEOUT"], $reqMsg);
			
	        if ($response === false) {
	            return false;
	        }

	        $xml = new SimpleXMLElement($response);
	
	        if(!$xml->Error) {
	            $res->setOk(true);
	            $res->setAcquirer($xml->Acquirer->acquirerID);
	            $issuerArray = array();
	            foreach ($xml->Directory->Issuer as $issuer) {
	                $issuerArray[(string)$issuer->issuerID] = (string)$issuer->issuerName;
	            }
	            $res->setIssuerList($issuerArray);
	            return $res;
	        } else {
	            $this->setError($xml->Error->consumerMessage);
	            return false;
	        }
	    }

	    public function processTrxRequest($request) {
	
	        if ($request->getMerchantId() == "")
	            $request->setMerchantId($this->_conf["MERCHANTID"]);
	        if ($request->getSubId() == "")
	            $request->setSubId($this->_conf["SUBID"]);
	        if ($request->getAuthentication() == "")
	            $request->setAuthentication($this->_conf["AUTHENTICATIONTYPE"]);
	        if ($request->getMerchantReturnUrl() == "")
	            $request->setMerchantReturnUrl($this->_conf["MERCHANTRETURNURL"]);
	        if ($request->getCurrency() == "")
	            $request->setCurrency($this->_conf["CURRENCY"]);
	        if ($request->getExpirationPeriod() == "")
	            $request->setExpirationPeriod($this->_conf["EXPIRATIONPERIOD"]);
	        if ($request->getLanguage() == "")
	            $request->setLanguage($this->_conf["LANGUAGE"]);
	        if ($request->getEntranceCode() == "")
	            $request->setEntranceCode($this->_conf["ENTRANCECODE"]);
	        if ($request->getDescription() == "")
	            $request->setDescription($this->_conf["DESCRIPTION"]);

	        $res = new Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerTrxResponse();
	
	        if (!$request->checkMandatory()) {
	            $res->setError(Mage::helper('idealadvanced')->__('Required fields missing'));
	            return $res;
	        }
	
	        $timestamp = $this->getGMTTimeMark();
	        $token = "";
	        $tokenCode = "";
	        if ( "SHA1_RSA" == $request->getAuthentication() ) {
	            $message = $timestamp
	                . $request->getIssuerId()
	                . $request->getMerchantId()
	                . $request->getSubId()
	                . $request->getMerchantReturnUrl()
	                . $request->getPurchaseId()
	                . $request->getAmount()
	                . $request->getCurrency()
	                . $request->getLanguage()
	                . $request->getDescription()
	                . $request->getEntranceCode();
	            $message = $this->_strip($message);
	
	            $token = $this->_security->createCertFingerprint($this->_conf["PRIVATECERT"]);
	
	            $tokenCode = $this->_security->signMessage($this->_conf["PRIVATEKEY"], $this->_conf["PRIVATEKEYPASS"], $message);

	            $tokenCode = base64_encode($tokenCode);
	        }
	
	        $reqMsg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
	                . "<AcquirerTrxReq xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">\n"
	                . "<createDateTimeStamp>" . utf8_encode($timestamp) .  "</createDateTimeStamp>\n"
	                . "<Issuer>" . "<issuerID>" . utf8_encode(htmlspecialchars($request->getIssuerId())) . "</issuerID>\n"
	                . "</Issuer>\n"
	                . "<Merchant>" . "<merchantID>" . utf8_encode(htmlspecialchars($request->getMerchantId())) . "</merchantID>\n"
	                . "<subID>" . utf8_encode($request->getSubId()) . "</subID>\n"
	                . "<authentication>" . utf8_encode($request->getAuthentication()) . "</authentication>\n"
	                . "<token>" . utf8_encode($token) . "</token>\n"
	                . "<tokenCode>" . utf8_encode($tokenCode) . "</tokenCode>\n"
	                . "<merchantReturnURL>" . utf8_encode(htmlspecialchars($request->getMerchantReturnUrl())) . "</merchantReturnURL>\n"
	                . "</Merchant>\n"
	                . "<Transaction>" . "<purchaseID>" . utf8_encode(htmlspecialchars($request->getPurchaseId())) . "</purchaseID>\n"
	                . "<amount>" . utf8_encode($request->getAmount()) . "</amount>\n"
	                . "<currency>" . utf8_encode($request->getCurrency()) . "</currency>\n"
	                . "<expirationPeriod>" . utf8_encode($request->getExpirationPeriod()) . "</expirationPeriod>\n"
	                . "<language>" . utf8_encode($request->getLanguage()) . "</language>\n"
	                . "<description>" . utf8_encode(htmlspecialchars($request->getDescription())) . "</description>\n"
	                . "<entranceCode>" . utf8_encode(htmlspecialchars($request->getEntranceCode())) . "</entranceCode>\n"
	                . "</Transaction>" . "</AcquirerTrxReq>";
	
	        $response = $this->_post($this->_conf["ACQUIRERURL"], $this->_conf["ACQUIRERTIMEOUT"], $reqMsg);
				
			
	        if ($response === false) {
	            return false;
	        }
	
	        $xml = new SimpleXMLElement($response);
	
	        if(!$xml->Error) {
	            $issuerUrl = (string)$xml->Issuer->issuerAuthenticationURL;
	            $transactionId = (string)$xml->Transaction->transactionID;
	            $res->setIssuerAuthenticationUrl($issuerUrl);
	            $res->setTransactionId($transactionId);
	            $res->setOk(true);
	            return $res;
	        } else {
	            $this->setError($xml->Error->consumerMessage);
	            return false;
	        }
	    }
	
	    public function processStatusRequest($request)
	    {
	        if ($request->getMerchantId() == "")
	            $request->setMerchantId($this->_conf["MERCHANTID"]);
	        if ($request->getSubId() == "")
	            $request->setSubId($this->_conf["SUBID"]);
	        if ($request->getAuthentication() == "")
	            $request->setAuthentication($this->_conf["AUTHENTICATIONTYPE"]);
	
	        $res = new Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerStatusResponse();
	
	        if (!$request->checkMandatory()) {
	            $$request->setErrorMessage(Mage::helper('idealadvanced')->__('Required fields missing'));
	            return $res;
	        }
	
	        $timestamp = $this->getGMTTimeMark();
	        $token = "";
	        $tokenCode = "";
	        if ("SHA1_RSA" == $request->getAuthentication()) {
	            $message = $timestamp . $request->getMerchantId() . $request->getSubId() . $request->getTransactionId();
	            $message = $this->_strip($message);
	
	            $token = $this->_security->createCertFingerprint($this->_conf["PRIVATECERT"]);
	            $tokenCode = $this->_security->signMessage( $this->_conf["PRIVATEKEY"], $this->_conf["PRIVATEKEYPASS"], $message );
	            $tokenCode = base64_encode($tokenCode);
	        }
	        $reqMsg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
	            . "<AcquirerStatusReq xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">\n"
	            . "<createDateTimeStamp>" . utf8_encode($timestamp) . "</createDateTimeStamp>\n"
	            . "<Merchant>" . "<merchantID>" . utf8_encode(htmlspecialchars($request->getMerchantId())) . "</merchantID>\n"
	            . "<subID>" . utf8_encode($request->getSubId()) . "</subID>\n"
	            . "<authentication>" . utf8_encode($request->getAuthentication()) . "</authentication>\n"
	            . "<token>" . utf8_encode($token) . "</token>\n"
	            . "<tokenCode>" . utf8_encode($tokenCode) . "</tokenCode>\n"
	            . "</Merchant>\n"
	            . "<Transaction>" . "<transactionID>" . utf8_encode(htmlspecialchars($request->getTransactionId())) . "</transactionID>\n"
	            . "</Transaction>" . "</AcquirerStatusReq>";
	
	        $response = $this->_post($this->_conf["ACQUIRERURL"], $this->_conf["ACQUIRERTIMEOUT"], $reqMsg);
			
	        if ($response === false) {
	            return false;
	        }
	
			
	        $xml = new SimpleXMLElement($response);
	        $status = (string)$xml->Transaction->status;
	        $creationTime = (string)$xml->createDateTimeStamp;
	        $transactionId = (string)$xml->Transaction->transactionID;
	        $consumerAccountNumber = (string)$xml->Transaction->consumerAccountNumber;
	        $consumerName = (string)$xml->Transaction->consumerName;
	        $consumerCity = (string)$xml->Transaction->consumerCity;
	
	        if ( strtoupper('Success') == strtoupper($status) ) {
	            $res->setAuthenticated(true);
	        } else {
	            $res->setAuthenticated(false);
	        }
	
	        $res->setTransactionStatus($status);
	        $res->setTransactionId($transactionId);
	        $res->setConsumerAccountNumber($consumerAccountNumber);
	        $res->setConsumerName($consumerName);
	        $res->setConsumerCity($consumerCity);
	        $res->setCreationTime($creationTime);
	
	        $message = $creationTime . $transactionId . $status . $consumerAccountNumber;
	        $message = trim( $message );
	
	        $signature64 = (string)$xml->Signature->signatureValue;
	
	        $sig = base64_decode($signature64);
	
	        $fingerprint = (string)$xml->Signature->fingerprint;
	
	        $certfile = $this->_security->getCertificateName($fingerprint, $this->_conf);
	
	        if($certfile == false) {
	            $res->setAuthenticated(false);
	            $res->setError('Fingerprint unknown.');
	            return $res;
	        }
	
	        $valid = $this->_security->verifyMessage($certfile, $message, $sig);
	
	        if( $valid != 1 ) {
	            $res->setAuthenticated(false);
	            $res->setError('Bad signature.');
	            return $res;
	        }
	
	        $res->setOk(true);
	        return $res;
	    }

	    public function getGMTTimeMark()
	    {
	        return gmdate('Y') . '-' . gmdate('m') . '-' . gmdate('d') . 'T'
	            . gmdate('H') . ':' . gmdate('i') . ':' . gmdate('s') . '.000Z';
	    }
	
	    protected function _post($url, $timeout, $dataToSend)
	    {
	        $_parsedUrl 		= parse_url($url);
	        $_sslReply 			= '';
	        $_sslErrorNumber 	= '';
	        $_sslErrorMessage 	= '';
	        
		    $_sslSocket 		= fsockopen('ssl://' . $_parsedUrl['host'], (empty($_parsedUrl['port']) ? 443 : intval($_parsedUrl['port'])), $_sslErrorNumber, $_sslErrorMessage, $timeout);
	
	        if($_sslSocket): 
	            $_headers = 'POST ' . (empty($_parsedUrl['path']) ? '/' : $_parsedUrl['path']) . (empty($_parsedUrl['query']) ? '' : '?' . $_parsedUrl['query']) . ' HTTP/1.0' . "\r\n";
	            $_headers .= 'Host: ' . $_parsedUrl['host'] . "\r\n";
	            $_headers .= 'Accept: text/html' . "\r\n";
	            $_headers .= 'Accept: charset=ISO-8859-1' . "\r\n";
	            $_headers .= 'Content-Length: ' . strlen($dataToSend) . "\r\n";
	            $_headers .= 'Content-Type: text/html; charset=ISO-8859-1' . "\r\n\r\n";
	
	            fputs($_sslSocket, $_headers, strlen($_headers));
	            fputs($_sslSocket, $dataToSend, strlen($dataToSend));
	
	            while(!feof($_sslSocket)):
	                $_sslReply .= @fgets($_sslSocket, 128);
	            endwhile;
	
	            fclose($_sslSocket);
	
	            if($_index = strpos($_sslReply, "\r\n\r\n")):
	                $_sslReply = substr($_sslReply, $_index + 4);
	            elseif($_index = strpos($_sslReply, "\n\n")):
	                $_sslReply = substr($_sslReply, $_index + 2);
	            endif;
	        else:
	            $this->setError($_sslErrorNumber . ':' . $_sslErrorMessage);
	            return false;
	        endif;

	        return $_sslReply;
	    }
	
	    protected function _strip($message)
	    {
	        $message = str_replace(' ', '', $message);
	        $message = str_replace("\t", '', $message);
	        $message = str_replace("\n", '', $message );
	        return $message;
	    }
	}