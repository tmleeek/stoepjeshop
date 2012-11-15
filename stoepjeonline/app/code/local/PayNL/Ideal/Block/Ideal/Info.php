<?php
/**
 * Pay.nl ideal info block
 *
 * @category    PayNL
 * @package     PayNL_Ideal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Ideal_Block_Ideal_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/ideal/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/ideal/pdf/info.phtml');
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
            $banksList = unserialize($this->getInfo()->getIdealBankList());
            return $banksList[$this->getInfo()->getIdealBankId()];
        } else {
        	  return $this->getInfo()->getIdealBankTitle();
        }
    }

}
?>
