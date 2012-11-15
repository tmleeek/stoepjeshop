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
class Jcode_IdealAdvanced_Model_Advanced 
	extends Mage_Payment_Model_Method_Abstract
	{
	    protected $_code  = 'idealadvanced';
	    protected $_formBlockType = 'idealadvanced/checkout_form';
	    protected $_infoBlockType = 'idealadvanced/checkout_info';
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
	
	    protected $_issuersList = null;
	
	    public function canUseCheckout()
	    {
	        if($this->getIssuerList() && parent::canUseCheckout()) {
	            return true;
	        } else {
	            return false;
	        }
	    }
	
	    public function getOrderPlaceRedirectUrl()
	    {
	          return Mage::getUrl('ideal/order/redirect', array('_secure' => true));
	    }
	
	    public function getApi()
	    {
	        return Mage::getSingleton('idealadvanced/api_advanced');
	    }
	
	    public function getIssuerList($saveAttrbute = false)
	    {
	        if ($this->_issuersList == null) {
	            $request = new Jcode_IdealAdvanced_Model_Api_Advanced_DirectoryRequest();
	            $response = $this->getApi()->processRequest($request, $this->getDebug());
	            if ($response) {
	                $this->_issuersList = $response->getIssuerList();
	                return $this->_issuersList;
	            } else {
	                $this->_issuersList = null;
	                $this->setError($this->getApi()->getError());
	                return false;
	            }
	        } else {
	            $this->getInfoInstance()
	                ->setIdealIssuerList(serialize($this->_issuersList))
	                ->save();
	            return $this->_issuersList;
	        }
	    }

	    public function validate()
	    {
	        parent::validate();
	        $paymentInfo = $this->getInfoInstance();
	        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
	            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
	        } else {
	            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
	        }
	
	        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
	            Mage::throwException(Mage::helper('idealadvanced')->__('Selected currency code (%s) is not compatabile with Ideal', $currency_code));
	        }
	
	        return $this;
	    }

	    public function sendTransactionRequest(Mage_Sales_Model_Order $order, $issuerId)
	    {
	        $request = new Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerTrxRequest();
	        $request->setIssuerID($issuerId);
	        $request->setPurchaseId($order->getIncrementId());
	        $request->setEntranceCode(Mage::helper('idealadvanced')->encrypt($order->getIncrementId()));
	        $request->setAmount($order->getBaseGrandTotal()*100);
	        $response = $this->getApi()->processRequest($request, $this->getDebug());

	        return $response;
	    }
	
	    public function getTransactionStatus($transactionId)
	    {
	        $request = new Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerStatusRequest();
	        $request->setTransactionId($transactionId);
	        $response = $this->getApi()->processRequest($request, $this->getDebug());
	        return $response;
	    }
	
	    public function capture(Varien_Object $payment, $amount)
	    {
	        $payment->setStatus(self::STATUS_APPROVED)
	            ->setLastTransId($this->getTransactionId());
	
	        return $this;
	    }
	
	    public function cancel(Varien_Object $payment)
	    {
	        $payment->setStatus(self::STATUS_DECLINED);
	
	        return $this;
	    }

	    public function transactionStatusCheck($shedule = null)
	    {
	        $gmtStamp = Mage::getModel('core/date')->gmtTimestamp();
	        $to = $this->getConfigData('cron_start') > 0?$this->getConfigData('cron_start'):1;
	        $to = date('Y-m-d H:i:s', $gmtStamp - $to * 3600);
	
	        $from = $this->getConfigData('cron_end') > 0?$this->getConfigData('cron_end'):1;
	        $from = date('Y-m-d H:i:s', $gmtStamp - $from * 86400);
	
	        $paymentCollection = Mage::getModel('sales/order_payment')->getCollection()
	            ->addAttributeToFilter('last_trans_id', array('neq' => ''))
	            ->addAttributeToFilter('method', $this->_code)
	            ->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to, 'datetime' => true))
	            ->addAttributeToFilter('ideal_transaction_checked', array('neq' => '1'));
	
	        $order = Mage::getModel('sales/order');
	        foreach($paymentCollection->getItems() as $item) {
	            $order->reset();
	            $order->load($item->getParentId());
	            $response = $this->getTransactionStatus($item->getLastTransId());
	
	            if ($response->getTransactionStatus() == Jcode_IdealAdvanced_Model_Api_Advanced::STATUS_SUCCESS) {
	                if ($order->canInvoice()) {
	                    $invoice = $order->prepareInvoice();
	                    $invoice->register()->capture();
	                    Mage::getModel('core/resource_transaction')
	                        ->addObject($invoice)
	                        ->addObject($invoice->getOrder())
	                        ->save();
	
	                    $order->addStatusToHistory($order->getStatus(), Mage::helper('idealadvanced')->__('Transaction Status Update: finished successfully'));
	                }
	            } else if ($response->getTransactionStatus() == Jcode_IdealAdvanced_Model_Api_Advanced::STATUS_CANCELLED) {
	                $order->cancel();
	                $order->addStatusToHistory($order->getStatus(), Mage::helper('idealadvanced')->__('Transaction Status Update: cancelled by customer'));
	            } else {
	                $order->cancel();
	                $order->addStatusToHistory($order->getStatus(), Mage::helper('idealadvanced')->__('Transaction Status Update: rejected by Ideal'));
	            }
	
	            $order->getPayment()->setIdealTransactionChecked(1);
	            $order->save();
	        }
	    }
	
	    public function getDebug()
	    {
	        return 0;
	    }
	}
