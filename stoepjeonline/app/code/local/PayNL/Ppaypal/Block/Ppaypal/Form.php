<?php
/**
 * Pay.nl show issuers for ppaypal
 *
 * @category    PayNL
 * @package     PayNL_Ppaypal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Ppaypal_Block_Ppaypal_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/ppaypal/form.phtml');
        parent::_construct();
    }
}
?>
