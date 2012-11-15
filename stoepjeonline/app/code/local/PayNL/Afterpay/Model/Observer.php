<?php

class PayNL_Afterpay_Model_Observer extends Mage_Core_Model_Abstract
{
    public function sales_quote_collect_totals_after(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $data = $observer->getInput();

        $quote->setExtraFee(0);
        $quote->setBaseExtraFee(0);

        foreach ($quote->getAllAddresses() as $address)
        {

            if ($address->getExtraFee()){
                $quote->setExtraFee((float) $address->getExtraFee());
            }
            if ($address->getBaseExtraFee()){
                $quote->setBaseExtraFee((float) $address->getBaseExtraFee());
            }
        }

        //$quote->save();
    }

    public function sales_order_payment_place_end(Varien_Event_Observer $observer)
    {
        $payment = $observer->getPayment();
        $pmName = $payment->getMethodInstance()->getCode();

        if ($pmName != 'afterpay_afterpay'){
            return;
        }

        $order = $payment->getOrder();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if (! $quote->getId()) {
           $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        $order->setExtraFee($quote->getExtraFee());
        $order->setBaseExtraFee($quote->getBaseExtraFee());
        $order->save();
    }
    
    /**
     * Performs order_creage_loadBlock response update
     * adds totals block to each response
     * This function is depricated, the totals block update is implemented
     * in phoenix/cashondelivery/sales.js (SalesOrder class extension)
     * @param Varien_Event_Observer $observer
     */

    public function controller_action_layout_load_before(Varien_Event_Observer $observer) {
        $action = $observer->getAction();
        if ($action->getFullActionName() != 'adminhtml_sales_order_create_loadBlock' || !$action->getRequest()->getParam('json')){
            return;
        }
        $layout = $observer->getLayout();
        $layout->getUpdate()->addHandle('adminhtml_sales_order_create_load_block_totals');
    }

}

?>