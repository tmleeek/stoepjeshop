<?php
/**
 * Pay.nl show issuers for cashticket
 *
 * @category    PayNL
 * @package     PayNL_Cashticket
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Cashticket_Block_Cashticket_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/cashticket/form.phtml');
        parent::_construct();
    }
}
?>
