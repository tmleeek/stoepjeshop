<?php
/**
 * Pay.nl show issuers for pincasso
 *
 * @category    PayNL
 * @package     PayNL_Pincasso
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Pincasso_Block_Pincasso_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/pincasso/form.phtml');
        parent::_construct();
    }
}
?>
