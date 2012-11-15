<?php
/**
 * Pay.nl iDEAL Advanced Checkout Controller
 *
 * @category    PayNL
 * @package     PayNL_Visamastercard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Visamastercard_VisamastercardController extends Mage_Core_Controller_Front_Action
{
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getVisamastercard()
    {
        return Mage::getSingleton('visamastercard/visamastercard');
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
            if(strlen($order->getVisamastercardTransactionId()) == 0)
            {
              $payment = $order->getPayment()->getMethodInstance();
              $quote = Mage::getSingleton('checkout/session')->getQuote();

              $response = $payment->createPayment($order);

              if ($response)
              {
                  $session->setVisamastercardVisamastercardQuoteId($session->getQuoteId());
                  $order->setVisamastercardTransactionId($response->getTransactionId());
                  $order->setVisamastercardPaidStatus(0);
                  $order->save();

                  $this->getResponse()->setBody(
                      $this->getLayout()->createBlock('visamastercard/visamastercard_redirect')
                          ->setMessage($this->__('You will be redirected to Pay.nl in a few seconds.'))
                          ->setRedirectUrl($response->getBankURL())
                          ->toHtml()
                  );

                  $session->unsQuoteId();
                  return;
              }
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('visamastercard/visamastercard_redirect')
                ->setMessage($this->__('Error occured. You will be redirected back to the store.'))
                ->setRedirectUrl(Mage::getUrl('checkout/cart'))
                ->toHtml()
        );
    }

    /**
     * When customer returns
     */
    public function returnAction()
    {
    	// Check order status!!!
	$transactionId = $this->getRequest()->get('orderId');

	$orderApi = Mage::getSingleton('visamastercard/api_visamastercard')->checkPayment($transactionId);
        $order = Mage::getModel('sales/order');
        $order->loadByAttribute('visamastercard_transaction_id', $transactionId);

        if ($orderApi->getPaidStatus() != true)
        {
            //
            $session = $this->getCheckout();
            $session->setQuoteId($session->getVisamastercardVisamastercardQuoteId(true));

            $this->_redirect('checkout/cart');
            return;
        }
        // NO Payment or late exchange request
        // TODO: create better way to handle this.
        $session = $this->getCheckout();
        $session->setQuoteId($session->getVisamastercardVisamastercardQuoteId(true));
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
        $this->getVisamastercard()->checkPayment($transaction_id,$action);
	echo "true|done";
    }
    
}
?>
