<?php
/**
 * Pay.nl show issuers for poverboeking
 *
 * @category    PayNL
 * @package     PayNL_Poverboeking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Poverboeking_Block_Poverboeking_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/poverboeking/form.phtml');
        parent::_construct();
    }
}
?>
