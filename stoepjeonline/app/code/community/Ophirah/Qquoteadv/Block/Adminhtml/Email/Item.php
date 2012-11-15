<?php
class Ophirah_Qquoteadv_Block_Adminhtml_Email_Item extends Mage_Checkout_Block_Cart_Abstract {
     /**
     * Get Product information from qquote_request_item table
     * @return object
     */
    public function getRequestedProductData($id, $quoteId) {
        $prices = array();
        $aQty   = array();
        
        $collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId)
                        ->addFieldToFilter('quoteadv_product_id', $id)
        ;
        $collection->getSelect()->order('request_qty asc');
        
        
        $n = count($collection);
        if ($n > 0) {
            foreach ($collection as $requested_item) {
                $aQty[]     = $requested_item->getRequestQty();
                $prices[]   = $requested_item->getOwnerBasePrice();
            }
        }
                
        return $return = array(
            'ownerPrices'=>$prices,
            'aQty'=>$aQty
        );
    }
}
