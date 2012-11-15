<?php
class Ophirah_Qquoteadv_Block_Adminhtml_Email_Items extends Mage_Sales_Block_Items_Abstract//Mage_Core_Block_Template
{

    public function getQuote() {
        $quoteId = $this->getRequest()->getParam('id');
        if(!$quoteId){
            $quoteObj   = $this->getData('quote');
            $quoteId    = $quoteObj->getQuoteId();
        }
        $quoteData = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId)
        //->load(true)
        ;

        foreach ($quoteData as $key => $quote) {
            $this->setQuoteId($quoteId);
            return $quote;
        }

        return;
    }

     /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getAllItems() {
        $collection = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteProduct($this->getQuoteId());

        return  $collection;
    }
}
