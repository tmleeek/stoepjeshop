<?php
/**
 * Pay.nl show issuers for fasterpay
 *
 * @category    PayNL
 * @package     PayNL_Fasterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Fasterpay_Block_Fasterpay_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/fasterpay/form.phtml');
        parent::_construct();
    }
}
?>
