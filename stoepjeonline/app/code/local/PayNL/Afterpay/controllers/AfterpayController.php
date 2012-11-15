<?php
/**
 * Pay.nl Afterpay Checkout Controller
 *
 * @category    PayNL
 * @package     PayNL_Afterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Afterpay_AfterpayController extends Mage_Core_Controller_Front_Action
{
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getAfterpay()
    {
        return Mage::getSingleton('afterpay/afterpay');
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
            if(strlen($order->getAfterpayTransactionId()) == 0)
            {
              $payment = $order->getPayment()->getMethodInstance();
              $quote = Mage::getSingleton('checkout/session')->getQuote();
	      
              $response = $payment->createPayment($order);
              
              if ($response)
              {
                  $session->setAfterpayAfterpayQuoteId($session->getQuoteId());
                  $order->setAfterpayTransactionId($response->getTransactionId());
                  $order->setAfterpayPaidStatus(0);
                  $order->save();

                  $this->getResponse()->setBody(
                      $this->getLayout()->createBlock('afterpay/afterpay_redirect')
                          ->setMessage($this->__('You will be redirected to your bank in a few seconds.'))
                          ->setRedirectUrl($response->getBankURL())
                          ->toHtml()
                  );

                  $session->unsQuoteId();
                  return;
              }
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('afterpay/afterpay_redirect')
                ->setMessage($this->__('Error occured. You will be redirected back to the store.'))
                ->setRedirectUrl(Mage::getUrl('checkout/cart'))
                ->toHtml()
        );
    }

    /**
     * When customer return from Pay
     */
    public function returnAction()
    {
        $session = $this->getCheckout();
        $session->setQuoteId($session->getAfterpayAfterpayQuoteId(true));
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
      $this->getAfterpay()->checkPayment($transaction_id,$action);
      echo "true|done";
    }    
}
?>
