<?php
/**
 * Pay.nl walliecard info block
 *
 * @category    PayNL
 * @package     PayNL_Walliecard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Walliecard_Block_Walliecard_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/walliecard/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/walliecard/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
