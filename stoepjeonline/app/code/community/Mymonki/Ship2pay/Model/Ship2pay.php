<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mymonki
 * @package    Mymonki_Ship2pay
 * @copyright  Copyright (c) 2010 Freshmind Sp. z o.o. (pawel@freshmind.pl)
 */

class Mymonki_Ship2pay_Model_Ship2pay extends Mage_Payment_Model_Method_Abstract
{
	
}

/*  protected $_code = 'bankpayment';

    protected $_formBlockType = 'bankpayment/form';
    protected $_infoBlockType = 'bankpayment/info';
    protected $_accounts;


    public function getAccountHolder()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getAccountHolder();
        }
        return null;
    }

    public function getAccountNumber()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getAccountNumber();
        }
        return null;
    }

    public function getSortCode()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getSortCode();
        }
        return null;
    }


    public function getBankName()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getBankName();
        }
        return null;
    }


    public function getIBAN()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getIBAN();
        }
        return null;
    }


    public function getBIC()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getBIC();
        }
        return null;
    }

    public function getPayWithinXDays()
    {
        return $this->getConfigData('paywithinxdays');
    }

    public function getCustomText($addNl2Br = true)
    {
        $customText = $this->getConfigData('customtext');
        if ($addNl2Br) {
            $customText = nl2br($customText);
        }
        return $customText;
    }

    public function getAccounts()
    {
        if (!$this->_accounts) {
            $paymentInfo = $this->getInfoInstance();
            if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
                $storeId = $paymentInfo->getOrder()->getStoreId();
            } else {
                $storeId = $paymentInfo->getQuote()->getStoreId();
            }

            $currentOrder = Mage::registry('current_order');
            $storeId = $currentOrder ? $currentOrder->getStoreId() : null;
            $accounts = unserialize(Mage::getStoreConfig('payment/bankpayment/bank_accounts',$storeId));

            $this->_accounts = array();
            $fields = is_array($accounts) ? array_keys($accounts) : null;
            if (!empty($fields)) {
                foreach ($accounts[$fields[0]] as $i => $k) {
                    if ($k) {
                        $account = new Varien_Object();
                        foreach ($fields as $field) {
                            $account->setData($field,$accounts[$field][$i]);
                        }
                        $this->_accounts[] = $account;
                    }
                }
            }
        }
        return $this->_accounts;
    }
}
*/
?>