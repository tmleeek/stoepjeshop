<?php
/**
 * Pay.nl directebanking info block
 *
 * @category    PayNL
 * @package     PayNL_Directebanking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Directebanking_Block_Directebanking_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/directebanking/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/directebanking/pdf/info.phtml');
        return $this->toHtml();
    }

    /**
     * Gets Bank Title from Payment Attribute
     *
     * @return string
     */
    public function getBankTitle()
    {
        if ($this->getInfo() instanceof Mage_Sales_Model_Quote_Payment) {
            $banksList = unserialize($this->getInfo()->getDirectebankingBankList());
            return $banksList[$this->getInfo()->getDirectebankingBankId()];
        } else {
        	  return $this->getInfo()->getDirectebankingBankTitle();
        }
    }

}
?>
