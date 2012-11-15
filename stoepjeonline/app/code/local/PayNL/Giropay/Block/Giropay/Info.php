<?php
/**
 * Pay.nl mrcash info block
 *
 * @category    PayNL
 * @package     PayNL_Giropay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Giropay_Block_Giropay_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/giropay/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/giropay/pdf/info.phtml');
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
            return $this->getInfo()->getGiropayBankId();
        } else {
            return $this->getInfo()->getGiropayBankId();
        }
    }
}
?>
