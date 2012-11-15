<?php
/**
 * Pay.nl iDEAL Advanced Checkout Controller
 *
 * @category    PayNL
 * @package     PayNL_Giropay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Giropay_GiropayController extends Mage_Core_Controller_Front_Action
{
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getGiropay()
    {
        return Mage::getSingleton('giropay/giropay');
    }
    /*
     * What to do when we get the redirect :)
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
        if($order->getId())
        {
            if(strlen($order->getGiropayTransactionId()) == 0)
            {
              $payment = $order->getPayment()->getMethodInstance();
              $quote = Mage::getSingleton('checkout/session')->getQuote();
              $bankid = $quote->getPayment()->getGiropayBankId();

              $response = $payment->createPayment($order,$bankid);

              if ($response)
              {
                  $session->setGiropayGiropayQuoteId($session->getQuoteId());
                  $order->setGiropayTransactionId($response->getTransactionId());
                  $order->setGiropayPaidStatus(0);
                  $order->save();

                  $this->getResponse()->setBody(
                      $this->getLayout()->createBlock('giropay/giropay_redirect')
                          ->setMessage($this->__('You will be redirected to Giropay in a few seconds.'))
                          ->setRedirectUrl($response->getBankURL())
                          ->toHtml()
                  );

                  $session->unsQuoteId();
                  return;
              }
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('giropay/giropay_redirect')
                ->setMessage($this->__('Error occured. You will be redirected back to the store.'))
                ->setRedirectUrl(Mage::getUrl('checkout/cart'))
                ->toHtml()
        );
    }

    /**
     * When customer return from iDEAL
     */
    public function returnAction()
    {
    	// Check order status!!!
	$transactionId = $this->getRequest()->get('orderId');

	$this->getGiropay()->checkPayment($transactionId);
	$orderApi = Mage::getSingleton('giropay/api_giropay')->checkPayment($transactionId);
        $order = Mage::getModel('sales/order');
        $order->loadByAttribute('giropay_transaction_id', $transactionId);

        if ($orderApi->getPaidStatus() != true)
        {
            //
            $session = $this->getCheckout();
            $session->setQuoteId($session->getGiropayGiropayQuoteId(true));

            $this->_redirect('checkout/cart');
            return;
        }
        // NO Payment or late exchange request
        // TODO: create better way to handle this.
        $session = $this->getCheckout();
        $session->setQuoteId($session->getGiropayGiropayQuoteId(true));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
    
    /**
     * Exchange receiver....
     */
    public function reportAction()
    {
	$transaction_id = $this->getRequest()->get('order_id');
        $action = $this->getRequest()->get('action');
        $this->getGiropay()->checkPayment($transaction_id,$action);
	echo "true|done";
    }
    
}
?>
