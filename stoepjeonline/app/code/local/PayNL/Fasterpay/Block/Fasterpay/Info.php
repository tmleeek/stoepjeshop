<?php
/**
 * Pay.nl fasterpay info block
 *
 * @category    PayNL
 * @package     PayNL_Fasterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Fasterpay_Block_Fasterpay_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/fasterpay/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/fasterpay/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
