<?php
/**
 * Pay.nl Directebanking checkout model
 *
 * @category    PayNL
 * @package     PayNL_Directebanking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Directebanking_Model_Directebanking extends Mage_Payment_Model_Method_Abstract
{
    const PAYMENT_TYPE_SALE = 'SALE';
    protected $_code  = 'directebanking_directebanking';
    protected $_formBlockType = 'directebanking/directebanking_form';
    protected $_infoBlockType = 'directebanking/directebanking_info';
    protected $_allowCurrencyCode = array('EUR');
    protected $_banksList = null;

    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function canUseCheckout()
    {
        if ($this->getBanksList() && parent::canUseCheckout()) {
            return true;
        } else {
            return false;
        }
    }

    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('directebanking/directebanking/redirect', array('_secure' => true));
    }

    /**
     * Get Pay.nl directebanking api Model
     *
     * @return PayNL_Directebanking_Model_Api_Directebanking
     */
    public function getApi()
    {
        return Mage::getSingleton('directebanking/api_directebanking');
    }

    public function getBanksList()
    {
        if ($this->_banksList == null)
        {
            $response = $this->getApi()->getBanks();
            if ($response)
            {
                $this->_banksList = $response;
                return $this->_banksList;
            }
            else
            {
                $this->_banksList = null;
                $this->setError('Unable to retrieve country list.');
                return false;
            }
        }
        else
        {
            $this->getInfoInstance()
                ->setDirectebankingBankList(serialize($this->_banksList))
                ->save();
            return $this->_banksList;
        }
    }

    /**
     * Make sure only the correct currency's are allowed for directebanking...
     * That being EUR only.
     *
     * @return bool
     */
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('directebanking')->__('Selected currency code ('.$currency_code.') is not compatible with Directebanking'));
        }
        return $this;
    }

    /**
     * Init the payment
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $bankId
     * @return boolean
     */
    public function createPayment(Mage_Sales_Model_Order $order, $bankId) 
    {
        $reportUrl = $this->getReportUrl();
        $returnUrl = $this->getReturnUrl();
        $data = $order->get();
        $email = $data['customer_email'];
       
        $arrOrderData = array();
        $arrOrderData['billingAddress'] = $order->getBillingAddress()->getData();

        if(is_object($order->getShippingAddress()))
        {
          $arrOrderData['shippingAddress'] = $order->getShippingAddress()->getData();
        }
        else
        {
          $arrOrderData['shippingAddress'] = $arrOrderData['billingAddress'];
        }
        
        $arrOrderData['order'] = $order->getData();
        $arrOrderData['orderItems'] = $order->getItemsCollection()->getData();

        
        return $this->getApi()->createPayment($bankId,
            $order->getBaseGrandTotal()*100, $returnUrl, $reportUrl, $email, $arrOrderData);
    }
    
    public function checkPayment($transactionId, $action="")
    {
        //when verified need to convert order into invoice
        $order = Mage::getModel('sales/order');
        $order->loadByAttribute('directebanking_transaction_id', $transactionId);

        $response = $this->getApi()->checkPayment($transactionId);

        if ($response === false)
        {
            echo "false|order not found in db";
            exit;
        }
        // Don't ask about next line... we're in the progress of moving to actual
        // types...
        if ($response->getPaidStatus() == true || $response->getPaidStatus() == "TRUE" ) {
            if (!$order->getId())
            {
                // Clueless on what TODO here...
            }
            else
            {
                // If we get these statusses, we should cancel the order.
                $arrActionDel = array();
                $arrActionDel[] = "delete";
                $arrActionDel[] = "incassodelete";
                $arrActionDel[] = "incassopredeclined";
                $arrActionDel[] = "incassostorno";
                if (in_array($action,$arrActionDel))
                {
                    // Cancel the order for Magento.
                    $payment = $order->getPayment();
                    $order->setStatus('refund');
                    $order->addStatusToHistory(
                                    $order->getStatus(), // keep order status/state
                                    Mage::helper('directebanking')->__('Refund', true),
                                    false);

                    $order->setDirectebankingPaidStatus(0);
                    $order->save();
                    // Send mail to admin, to make sure this is known
                    $mail = new Zend_Mail('utf-8');
                    $salesidentity = Mage::getStoreConfig('sales_email/order/identity', $this->getStoreId());
                    $emailname = Mage::getStoreConfig('trans_email/ident_'.$salesidentity.'/name', $this->getStoreId());
                    $emailaddr = Mage::getStoreConfig('trans_email/ident_'.$salesidentity.'/email', $this->getStoreId());
                    $fromname = Mage::getStoreConfig('trans_email/ident_general/name',$this->getStoreId());
                    $fromaddr = Mage::getStoreConfig('trans_email/ident_general/email',$this->getStoreId());

                    $mail->addTo($emailaddr,$emailaddr);
                    $mail->setSubject(Mage::helper('directebanking')->__('Order #%s refunded', $order->getRealOrderId()));
                    $mail->setFrom($fromaddr,$fromname);

                    $body = Mage::helper('directebanking')->__("The order: #%s has been refunded.\nPay.nl information:\nOrder code: %s\nNo further notifications for this will be available.",$order->getRealOrderId(),$transactionId);
                    $mail->setBodyText($body);
                    $mail->send();
                    return true;
                }
                $order->setDirectebankingPaidStatus(1);
                $orderAmount = $order->getBaseGrandTotal()*100;
                $directebankingAmount = $response->getAmount();
                if ($directebankingAmount!=$orderAmount && false)
                {
                    // TODO: create more stuff to handle this.
                    $order->addStatusToHistory(
                        $order->getStatus(),//continue setting current order status
                        Mage::helper('directebanking')->__('Order total amount does not match directebanking gross total amount')
                    );
                    $order->save();
                }
                else
                {
                    // get from config order status to be set
                    $newOrderStatus = $this->getConfigData('order_status',
                        $order->getStoreId());
                    if (empty($newOrderStatus)) {
                        $newOrderStatus = $order->getStatus();
                    }

                    if (!$order->canInvoice()) {
                       // todo, fixme, what am I supposed to do here?
                    } else {
                       //need to save transaction id
                       $order->getPayment()->setTransactionId($transactionId);
                       //convert order into invoice
                       $invoice = $order->prepareInvoice();
                       $invoice->register()->capture();
                       Mage::getModel('core/resource_transaction')
                           ->addObject($invoice)
                           ->addObject($invoice->getOrder())
                           ->save();
                       $order->setStatus('Processing');
                       $order->setIsInProcess(true);
                       $order->setState(
                           Mage_Sales_Model_Order::STATE_PROCESSING, $newOrderStatus,
                           Mage::helper('directebanking')->__('Invoice #%s created', $invoice->getIncrementId()),
                           $notified = true
                       );
                    }
                    $order->save();
                    if(!$order->getEmailSent())
                    {
                        $order->sendNewOrderEmail();
                    }

                    $order->setEmailSent(true);
                    $order->save();
                }
            }
        } else {
            if (!$order->getId())
            {
                // Some fcked the id's up... Dunno how to handle this.
            } else {
		if ($response->getFinal())
		{
                    $this->cancel($order);
                    $order->setStatus('Canceled');
                    $order->addStatusToHistory(
                                $order->getStatus(), // keep order status/state
                                Mage::helper('pincasso')->__('Declined by Pay.nl', true),
                                false);
                    $order->cancel();
		}
                $order->setDirectebankingPaidStatus(0);
                $order->save();
            }
        }
    }

    public function cancel(Varien_Object $payment)
    {
        $payment->setStatus(self::STATUS_DECLINED);

        return $this;
    }

    // Getters & setters & default stuff
    public function getDebug()
    {
        return $this->getConfigData('debug_flag');
    }
    
    public function getReturnUrl() {
        return Mage::getUrl('directebanking/directebanking/return');
    }
    
    public function getReportUrl() {
        return Mage::getUrl('directebanking/directebanking/report');
    }

    public function isInitializeNeeded()
    {
        return true;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_directebanking');
        $stateObject->setIsNotified(false);
    }

    public function canUseForMultishipping()
    {
        return false;
    }

    public function canCapture()
    {
        return true;
    }
    public function canCapturePartial()
    {
        return true;
    }

} 
?>