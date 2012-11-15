<?php
/**
 * Pay.nl cashticket info block
 *
 * @category    PayNL
 * @package     PayNL_Cashticket
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Cashticket_Block_Cashticket_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/cashticket/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/cashticket/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
