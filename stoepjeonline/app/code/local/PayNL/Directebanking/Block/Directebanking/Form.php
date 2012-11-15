<?php
/**
 * Pay.nl show issuers for directebanking
 *
 * @category    PayNL
 * @package     PayNL_Directebanking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Directebanking_Block_Directebanking_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/directebanking/form.phtml');
        parent::_construct();
    }

    /**
     * Return array that contains countries
     *
     * @return array
     */
    public function getBanksList()
    {
        return $this->getMethod()->getBanksList();
    }
}
?>
