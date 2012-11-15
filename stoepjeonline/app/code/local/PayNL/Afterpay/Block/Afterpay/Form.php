<?php
/**
 * Pay.nl show issuers for afterpay 
 *
 * @category    PayNL
 * @package     PayNL_Afterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Afterpay_Block_Afterpay_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/afterpay/form.phtml');
        parent::_construct();
    }
}
?>
