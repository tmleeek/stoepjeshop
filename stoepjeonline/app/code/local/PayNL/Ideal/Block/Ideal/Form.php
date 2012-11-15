<?php
/**
 * Pay.nl show issuers for ideal
 *
 * @category    PayNL
 * @package     PayNL_Ideal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Ideal_Block_Ideal_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/ideal/form.phtml');
        parent::_construct();
    }

    /**
     * Return array that contains issuer list
     *
     * @return array
     */
    public function getBanksList()
    {
        return $this->getMethod()->getBanksList();
    }
}
?>
