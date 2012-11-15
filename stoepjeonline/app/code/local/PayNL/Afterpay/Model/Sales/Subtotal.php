<?php

class PayNL_Afterpay_Model_Sales_Subtotal extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $param = Mage::app()->getFrontController()->getRequest()->getParam('payment');

        $pmName = isset($param['method']) ? $param['method'] : null;

        if ($pmName != 'afterpay_afterpay'
                && (!count($address->getQuote()->getPaymentsCollection())
                        ||
                    !$address->getQuote()->getPayment()->hasMethodInstance())
                   )
        {
            return $this;
        }

        $paymentMethod = $address->getQuote()->getPayment()->getMethodInstance();

        if ($paymentMethod->getCode() != 'afterpay_afterpay')
            return $this;
        $pmName = $paymentMethod->getCode();

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        $baseTotal = $address->getBaseGrandTotal();

        $quote = $address->getQuote();
        $store = $quote->getStore();

        $baseExtraFee = $paymentMethod->getFee();
        $baseTotal += $baseExtraFee;

        $address->setBaseExtraFee($baseExtraFee);
        $address->setExtraFee($store->convertPrice($baseExtraFee, false));

        // update totals
        $address->setBaseGrandTotal($baseTotal);

        $address->setGrandTotal($store->convertPrice($baseTotal, false));
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $amount = $address->getExtraFee();
        if ($amount != 0) {
            try
            {
                $paymentMethod = $address->getQuote()->getPayment()->getMethodInstance();
            }
            catch (Exception $ex)
            {
                return $this;
            }
            if ($paymentMethod->getCode() != 'afterpay_afterpay')
                return $this;
            //if (!in_array($this->getCode(), $this->getStandard()->payment_methods)) return $this;

            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('afterpay')->__($paymentMethod->getTitle()) . " " . Mage::helper('afterpay')->__('fee'),
                'value' => $amount,
            ));
        }

        return $this;
    }

}