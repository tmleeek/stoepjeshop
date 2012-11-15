<?php
/**
 * Pay.nl ccard info block
 *
 * @category    PayNL
 * @package     PayNL_Ccard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Ccard_Block_Ccard_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/ccard/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/ccard/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
