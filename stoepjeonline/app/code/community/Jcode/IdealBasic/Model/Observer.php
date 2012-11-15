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
class Jcode_IdealBasic_Model_Observer
{
    /**
     * Convert specific attributes from Quote Payment to Order Payment
     *
     * @param Varien_Object $observer
     * @return Mage_Ideal_Model_Observer
     */
    public function convertPayment($observer)
    {
        $orderPayment = $observer->getEvent()->getOrderPayment();
        $quotePayment = $observer->getEvent()->getQuotePayment();
        $orderPayment->setIdealIssuerId($quotePayment->getIdealIssuerId());

        if ($quotePayment->getIdealIssuerId()) {
            $issuerList = unserialize($quotePayment->getIdealIssuerList());
            if (isset($issuerList[$quotePayment->getIdealIssuerId()])) {
                $orderPayment->setIdealIssuerTitle(
                    $issuerList[$quotePayment->getIdealIssuerId()]
                );
            }
        }
        return $this;
    }
}
