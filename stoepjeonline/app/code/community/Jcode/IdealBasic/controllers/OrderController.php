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

class Jcode_IdealBasic_OrderController
	extends Mage_Core_Controller_Front_Action
	{
	    /**
	     *  Return order instance for last real order ID (stored in session)
	     *
	     *  @param    none
	     *  @return	  Mage_Sales_Model_Entity_Order object
	     */
	    protected function _getOrder ()
	    {
	        $order = Mage::getModel('sales/order');
	        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
	
	        if (!$order->getId()) {
	            return false;
	        }
	
	        return $order;
	    }
	
	    /**
	     * When a customer chooses iDEAL Basic on Checkout/Payment page
	     */
	    public function redirectAction()
	    {
	        $session = Mage::getSingleton('checkout/session');
	        $session->setIdealBasicQuoteId($session->getQuoteId());
	        $session->setIdealBasicOrderId($session->getLastOrderId());
	
	        if (!($order = $this->_getOrder())) {
	            $this->norouteAction();
	            return;
	        }
	        $order->addStatusToHistory(
	            $order->getStatus(),
	            $this->__('Customer was redirected to iDEAL. Please, check the status of a transaction via the ING iDEAL Dashboard before delivery of the goods purchased.')
	        );
	        $order->save();
	
	        $this->getResponse()->setBody(
	            $this->getLayout()->createBlock('idealbasic/checkout_redirect')
	                ->setOrder($order)
	                ->toHtml()
	            );
	        $session->unsQuoteId();
	        $session->unsLastOrderId();
	    }
	
	    /**
	     *  Success response from iDEAL
	     *
	     *  @return	  void
	     */
	    public function  successAction()
	    {
	        $session = Mage::getSingleton('checkout/session');
	        $session->setLastOrderId($session->getIdealBasicOrderId(true));


	        $order = $this->_getOrder();
	        if (!$session->getLastOrderId()) {
	           $this->norouteAction();
	            return false;
	        }
	
	        $session->setQuoteId($session->getIdealBasicQuoteId(true));
	
	        $order->addStatusToHistory(
	            $order->getStatus(),
	            $this->__('Customer successfully returned from iDEAL')
	        );
	
	        $order->sendNewOrderEmail();
	
	        $this->_saveInvoice($order);
	
	        $order->save();
	
	        $this->_redirect('checkout/onepage/success');
	    }
	
	    /**
	     *  Cancel response from iDEAL
	     *
	     *  @return	  void
	     */
	    public function cancelAction()
	    {
	        $session = Mage::getSingleton('checkout/session');
	        $session->setLastOrderId($session->getIdealBasicOrderId(true));
	
	        $order = $this->_getOrder();
	        if (!$session->getLastOrderId()) {
	            $this->norouteAction();
	            return false;
	        }
	
	        $order->cancel();
	
	        $history = $this->__('Payment was canceled by customer');
	
	        $order->addStatusToHistory(
	            $order->getStatus(),
	            $history
	        );
	
	        $order->save();
	
	        $session->setQuoteId($session->getIdealBasicQuoteId(true));
			(Mage::getSingleton('customer/session')->isLoggedIn())
				?	$this->_redirect('customer/account')
        		:	$this->_redirect('checkout/cart');
	    }
	
	
	    /**
	     *  Error response from iDEAL
	     *
	     *  @return	  void
	     */
	    public function failureAction ()
	    {
	        $session = Mage::getSingleton('checkout/session');
	        $session->setLastOrderId($session->getIdealBasicOrderId(true));
	
	        $order = $this->_getOrder();
	
	        if (!$order->getId()) {
	            $this->norouteAction();
	            return false;
	        }
	
	        $order->cancel();
	
	        $history = $this->__('Error occured with transaction %s.', $order->getIncrementId()) . ' '
	                 . $this->__('Customer was returned from iDEAL.');
	
	        $order->addStatusToHistory(
	            $order->getStatus(),
	            $history
	        );
	
	        $order->save();
	
	        $session->setQuoteId($session->getIdealBasicQuoteId(true));
	        $session->setIdealErrorMessage($this->__('An error occurred while processing your iDEAL transaction. Please contact the web shop or try again later. Transaction number is %s.', $order->getIncrementId()));
	
	        $this->loadLayout();
	        $this->renderLayout();
	
	    }
	
	    /**
	     * Notification action that calling by iDEAL
	     *
	     */
	    public function notifyAction()
	    {
	        if (isset($HTTP_RAW_POST_DATA)) {
	            $xmlResponse = $HTTP_RAW_POST_DATA;
	        } else {
	            $xmlResponse = file_get_contents("php://input");
	        }
	
	        if (!strlen($xmlResponse)) {
	            $this->norouteAction();
	            return;
	        }
	
	        $xmlObj = simplexml_load_string($xmlResponse);
	        $status = (string)$xmlObj->status;
	
	        $order = Mage::getModel('sales/order')
	            ->loadByIncrementId((int)$xmlObj->purchaseID);
	
	        if (!$order->getId()) {
	            return;
	        }
	
	        if ($status == 'Success') {
	            if (!$order->hasInvoices()) {
	                $this->_saveInvoice($order);
	                $order->addStatusToHistory($order->getStatus(),
	                    $this->__('Notification from iDEAL was recived with status %s. Invoice was created. Please, check the status of a transaction via the ING iDEAL Dashboard before delivery of the goods purchased.', $status)
	                );
	            } else {
	                $order->addStatusToHistory($order->getStatus(),
	                    $this->__('Notification from iDEAL was recived with status %s.', $status)
	                );
	            }
	        } else {
	            $order->addStatusToHistory($order->getStatus(),
	                $this->__('Notification from iDEAL was recived with status %s.', $status)
	            );
	            $order->cancel();
	        }
	
	        $order->save();
	    }
	
	    /**
	     *  Save invoice for order
	     *
	     *  @param    Mage_Sales_Model_Order $order
	     *  @return	  boolean Can save invoice or not
	     */
	    protected function _saveInvoice(Mage_Sales_Model_Order $order)
	    {
	        if ($order->canInvoice()) {
	            $invoice = $order->prepareInvoice();
	            $invoice->register()->capture();
	            Mage::getModel('core/resource_transaction')
	               ->addObject($invoice)
	               ->addObject($invoice->getOrder())
	               ->save();
	            return true;
	        }
	        return false;
	    }
	}
	