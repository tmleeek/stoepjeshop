<?php
/**
 * Pay.nl afterpay info block
 *
 * @category    PayNL
 * @package     PayNL_Afterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Afterpay_Block_Afterpay_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/afterpay/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/afterpay/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
