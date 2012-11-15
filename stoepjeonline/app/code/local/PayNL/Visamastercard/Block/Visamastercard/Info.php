<?php
/**
 * Pay.nl info block
 *
 * @category    PayNL
 * @package     PayNL_Visamastercard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Visamastercard_Block_Visamastercard_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/visamastercard/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/visamastercard/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
