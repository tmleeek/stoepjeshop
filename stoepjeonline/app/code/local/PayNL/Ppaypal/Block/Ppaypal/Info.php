<?php
/**
 * Pay.nl ppaypal info block
 *
 * @category    PayNL
 * @package     PayNL_Ppaypal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Ppaypal_Block_Ppaypal_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/ppaypal/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/ppaypal/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
