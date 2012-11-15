<?php
/**
 * Pay.nl iDEAL Advanced Checkout Controller
 *
 * @category    PayNL
 * @package     PayNL_Mrcash
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Mrcash_MrcashController extends Mage_Core_Controller_Front_Action
{
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getMrcash()
    {
        return Mage::getSingleton('mrcash/mrcash');
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
            if(strlen($order->getMrcashTransactionId()) == 0)
            {
              $payment = $order->getPayment()->getMethodInstance();
              $quote = Mage::getSingleton('checkout/session')->getQuote();

              $response = $payment->createPayment($order);

              if ($response)
              {
                  $session->setMrcashMrcashQuoteId($session->getQuoteId());
                  $order->setMrcashTransactionId($response->getTransactionId());
                  $order->setMrcashPaidStatus(0);
                  $order->save();

                  $this->getResponse()->setBody(
                      $this->getLayout()->createBlock('mrcash/mrcash_redirect')
                          ->setMessage($this->__('You will be redirected to MisterCash in a few seconds.'))
                          ->setRedirectUrl($response->getBankURL())
                          ->toHtml()
                  );

                  $session->unsQuoteId();
                  return;
              }
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mrcash/mrcash_redirect')
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

	$orderApi = Mage::getSingleton('mrcash/api_mrcash')->checkPayment($transactionId);
        $order = Mage::getModel('sales/order');
        $order->loadByAttribute('mrcash_transaction_id', $transactionId);

        if ($orderApi->getPaidStatus() != true)
        {
            //
            $session = $this->getCheckout();
            $session->setQuoteId($session->getMrcashMrcashQuoteId(true));

            $this->_redirect('checkout/cart');
            return;
        }
        // NO Payment or late exchange request
        // TODO: create better way to handle this.
        $session = $this->getCheckout();
        $session->setQuoteId($session->getMrcashMrcashQuoteId(true));
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
        $this->getMrcash()->checkPayment($transaction_id,$action);
	echo "true|done";
    }
    
}
?>
