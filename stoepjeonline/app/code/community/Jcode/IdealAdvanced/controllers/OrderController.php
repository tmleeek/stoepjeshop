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
class Jcode_IdealAdvanced_OrderController 
	extends Mage_Core_Controller_Front_Action
	{
	    public function getCheckout()
	    {
	        return Mage::getSingleton('checkout/session');
	    }

	    public function redirectAction()
	    {
	        $order = Mage::getModel('sales/order');
	        $order->load($this->getCheckout()->getLastOrderId());
	        if($order->getId()){
	            $advanced = $order->getPayment()->getMethodInstance();
	            $issuerId = $order->getPayment()->getIdealIssuerId();
	
	            $response = $advanced->sendTransactionRequest($order, $issuerId);
	
	            if ($response) {
	                $order->getPayment()->setTransactionId($response->getTransactionId());
	                $order->getPayment()->setLastTransId($response->getTransactionId());
	                $order->getPayment()->setIdealTransactionChecked(0);
	
	                if ($response->getError()) {
	                    $this->getCheckout()->setIdealErrorMessage($response->getError());
	                    $this->_redirect('*/*/failure');
	                    return;
	                }
	
	                $this->getResponse()->setBody(
	                    $this->getLayout()->createBlock('idealadvanced/checkout_redirect')
	                        ->setMessage($this->__('You will be redirected to bank in a few seconds.'))
	                        ->setRedirectUrl($response->getIssuerAuthenticationUrl())
	                        ->toHtml()
	                );
	
	                $order->addStatusToHistory(
	                    $order->getStatus(),
	                    $this->__('Customer was redirected to Ideal')
	                );
	                $order->save();
	
	                $this->getCheckout()->setIdealAdvancedQuoteId($this->getCheckout()->getQuoteId(true));
	                $this->getCheckout()->setIdealAdvancedOrderId($this->getCheckout()->getLastOrderId(true));
	
	
	                return;
	            }
	        }
	
	        $this->getResponse()->setBody(
	            $this->getLayout()->createBlock('idealadvanced/checkout_redirect')
	                ->setMessage($this->__('Error occured. You will be redirected back to store.'))
	                ->setRedirectUrl(Mage::getUrl('checkout/cart'))
	                ->toHtml()
	        );
	    }
	
	    /**
	     * When a customer cancels payment from iDEAL
	     */
	    public function cancelAction()
	    {
	        $order = Mage::getModel('sales/order');
	        $this->getCheckout()->setLastOrderId($this->getCheckout()->getIdealAdvancedOrderId(true));
	        $order->load($this->getCheckout()->getLastOrderId());
	
	        if (!$order->getId()) {
	            $this->norouteAction();
	            return;
	        }
	
	        $order->addStatusToHistory(
	            $order->getStatus(),
	            $this->__('Customer canceled payment.')
	        );
	        $order->cancel();
	        $order->save();
	
	        $$this->getCheckout()->setQuoteId($$this->getCheckout()->setIdealAdvancedQuoteId(true));
	        $this->_redirect('checkout/cart');
	    }
	
	    /**
	     * When customer return from iDEAL
	     */
	    public function  resultAction()
	    {
	        /**
	         * Decrypt Real Order Id that was sent encrypted
	         */
	        $orderId = Mage::helper('idealadvanced')->decrypt($this->getRequest()->getParam('ec'));
	        $transactionId = $this->getRequest()->getParam('trxid');
	
	        $order = Mage::getModel('sales/order');
	        $order->loadByIncrementId($orderId);
	
	        if ($order->getId() > 0) {
	            $advanced = $order->getPayment()->getMethodInstance();
	            $advanced->setTransactionId($transactionId);
	            $response = $advanced->getTransactionStatus($transactionId);
	
	            $this->getCheckout()->setQuoteId($this->getCheckout()->getIdealAdvancedQuoteId(true));
	            $this->getCheckout()->setLastOrderId($this->getCheckout()->getIdealAdvancedOrderId(true));
	
	            if ($response->getTransactionStatus() == Jcode_IdealAdvanced_Model_Api_Advanced::STATUS_SUCCESS) {
	                $this->getCheckout()->getQuote()->setIsActive(false)->save();
	
	                if ($order->canInvoice()) {
	                    $invoice = $order->prepareInvoice();
	                    $invoice->register()->capture();
	                    Mage::getModel('core/resource_transaction')
	                        ->addObject($invoice)
	                        ->addObject($invoice->getOrder())
	                        ->save();
	
	                    $order->addStatusToHistory($order->getStatus(), Mage::helper('idealadvanced')->__('Customer successfully returned from iDEAL'));
	                }
	
	                $order->sendNewOrderEmail();
	
	                $this->_redirect('checkout/onepage/success');
	            } else if ($response->getTransactionStatus() == Jcode_IdealAdvanced_Model_Api_Advanced::STATUS_CANCELLED) {
	                $order->cancel();
	                $order->addStatusToHistory($order->getStatus(), Mage::helper('idealadvanced')->__('Customer cancelled payment'));
	
	                $this->_redirect('checkout/cart');
	            } else {
	                $order->cancel();
	                $order->addStatusToHistory($order->getStatus(), Mage::helper('idealadvanced')->__('Customer was rejected by Ideal'));
	                $this->getCheckout()->setIdealErrorMessage(
	                    Mage::helper('idealadvanced')->__('An error occurred while processing your iDEAL transaction. Please contact the web shop or try
	again later. Transaction number is %s.', $order->getIncrementId())
	                );
	
	                $this->_redirect('*/*/failure');
	            }
	            $order->getPayment()->setIdealTransactionChecked(1);
	            $order->save();
	        } else {
	            $this->_redirect('checkout/cart');
	        }
	    }
	
	    /**
	     * Redirected here when customer returns with error
	     */
	    public function failureAction()
	    {
	        if (!$this->getCheckout()->getIdealErrorMessage()) {
	            $this->norouteAction();
	            return;
	        }
			$this->_redirect('checkout/onepage/failure');
	        #$this->loadLayout();
	        #$this->renderLayout();
	    }
	}
