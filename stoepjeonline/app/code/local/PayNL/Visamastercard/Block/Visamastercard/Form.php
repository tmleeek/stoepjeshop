<?php
/**
 * Pay.nl 
 *
 * @category    PayNL
 * @package     PayNL_Visamastercard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Visamastercard_Block_Visamastercard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/visamastercard/form.phtml');
        parent::_construct();
    }
}
?>
