<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Block_Multifees_Fee_Checkout_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	private $_id;

	public function __construct()
	{
		parent::__construct();
        $this->setTemplate('multifees/checkout-form.phtml');
        $this->_id = (int) $this->getRequest()->getParam('id');

        $this->initForm();
	}

	public function _getHelper()
	{
		return Mage::helper('multifees');
	}

	protected function initForm()
	{
		$helper = $this->_getHelper();
		$form = new Varien_Data_Form(array(
			'id'      => 'edit_form',
			'action'  => $this->getUrl('*/*/save', array('id' => $this->_id)),
			'method'  => 'post',
			)
	    );

	    $form->addField('checkout_type', 'hidden', array(
            'name'      => 'fee[checkout_type]',
            'value'     => 1,
        ));

        $form->addField('required', 'hidden', array(
            'name'      => 'fee[required]',
            'value'     => MageWorx_MultiFees_Helper_Data::REQUIRED_YES,
        ));

	    if (!Mage::app()->isSingleStoreMode()) {
	    	$form->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'required'  => true,
	    		'class'     => 'multifees-input-long',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        } else {
            $form->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
		}

		$form->addField('input_type', 'select', array(
		    'name'      => 'fee[input_type]',
			'class'     => 'multifees-input-long ',
		    'onchange'  => 'feeCheckout.checkInType(this)',
		    'required'  => true,
		    'values'    => $helper->setFieldOfForm($helper->getTypeArray(false, true), true),
		));

		$form->addField('payment', 'multiselect', array(
		    'name'      => 'checkout_method[]',
            'class'     => 'multifees-input-long ',
            'required'  => true,
		    'values'    => $this->_preparePaymentMethods(),
		));

		$form->addField('shipping', 'multiselect', array(
		    'name'      => 'checkout_method[]',
            'class'     => 'multifees-input-long ',
            'required'  => true,
		    'values'    => $this->_prepareShippingMethods(),
		));

		$form->addField('status', 'select', array(
		    'name'      => 'fee[status]',
			'class'     => 'multifees-input-full',
		    'values'    => $helper->setFieldOfForm($helper->getStatusArray()),
		));

		if ($this->_id) {
			$formData  = array();
			$storeData = Mage::getResourceSingleton('multifees/store')->getFee($this->_id);
			$model     = Mage::getSingleton('multifees/fee')->load($this->_id);

			if ($model->getData()) {
				$singleStore = false;
				if (Mage::app()->isSingleStoreMode()) {
					$singleStore = true;
				}
				$formData = array_merge($model->getData(), $this->_prepareStoreOfForm($storeData, $singleStore));
			} else {
				$formData = $model->getData();
			}
			Mage::register('input_type', $model->getData('input_type'));
			$form->setValues($formData);
		}
		$this->setForm($form);

		return $this;
	}

	private function _prepareShippingMethods()
	{
        $methods = Mage::getSingleton('adminhtml/system_config_source_shipping_allmethods')->toOptionArray();
        if (isset($methods[0])) {
            unset($methods[0]);
        }
        return $methods;
	}

    private function _preparePaymentMethods()
    {
        $methods = Mage::getSingleton('adminhtml/system_config_source_payment_allowedmethods')->toOptionArray();
        if (isset($methods[0])) {
            unset($methods[0]);
        }
        return $methods;
    }

    public function getCheckoutMethods($type = null)
    {
    	if (is_null($type)) {
    		return;
    	}
    	$values = array();
    	if ($type == 'payment') {
    		$values = $this->_preparePaymentMethods();
    	} elseif ($type == 'shipping') {
    		$values = $this->_prepareShippingMethods();
    	}
        $form = new Varien_Data_Form();
        $checkout = $form->addField('checkout_method', 'multiselect', array(
                'name'     => 'checkout_method[]',
                'values'   => $values,
                'class'    => 'multifees-input-long',
                'required' => true,
        ));
        $checkout->setRenderer($this->getLayout()->createBlock('adminhtml/catalog_form_renderer_fieldset_element'));

        return preg_replace('/\r?\n+/', '', $checkout->toHtml());
    }

	private function _prepareStoreOfForm($storeData, $singleStore = false)
	{
		$result = array();
		if ($storeData) {
			if (false === $singleStore) {
				foreach ($storeData as $value) {
					$result[] = $value['store_id'];
				}
				$result = array('store_id' => $result);
			} else {
				foreach ($storeData as $value) {
					$storeId = $value['store_id'];
				}
				$result = array('store_id' => $storeId);
			}
		}
		return $result;
	}

	public function getFromElement($elementId)
	{
		return $this->getForm()->getElement($elementId)->toHtml();
	}

	public function getLabelValues()
    {
        $values = array();
        $titles = $this->_getLabel();
   		$stores = $this->getStores();
   		foreach ($stores as $store) {
        	$values[$store->getStoreId()] = isset($titles[$store->getStoreId()]) ? $titles[$store->getStoreId()] : '';
   		}
        return $values;
    }

    private function _getLabel()
    {
    	$result = array();
    	if ($this->_id) {
	    	$data = Mage::getResourceSingleton('multifees/language_fee')->getFee($this->_id);
	    	if ($data) {
	    		foreach ($data as $value) {
	    			if (!empty($value['title'])) {
			   			$result[$value['store_id']] = $value['title'];
	    			}
	    		}
	    	}
    	}
    	return $result;
    }

    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }

	public function getOptionValues()
    {
		$values = null;
		if ($this->_id) {
			$collection = Mage::getResourceModel('multifees/option_collection')
				->addFeeFilter($this->_id)
				->getItems();

			foreach ($collection as $option) {
                $value = array();
                $value['id']         = $option->getId();
                $value['price']      = Mage::app()->getStore()->roundPrice($option->getPrice());
                $value['price_type'] = $option->getPriceType();

                $stores = $this->getStoreOptionValues($option->getOption());
                foreach ($this->getStores() as $store) {
                    $storeValues = $stores[$store->getStoreId()];
                    if (isset($storeValues[$option->getId()])) {
                        $value['store'][$store->getStoreId()] = $storeValues[$option->getId()];
                    } else {
                        $value['store'][$store->getStoreId()] = '';
                    }
                }
                $values = $value;
            }
    	}
        return new Varien_Object($values);
    }

	public function getStoreOptionValues($options)
    {
		$values = array();
		if ($options) {
			foreach ($this->getStores() as $store) {
				foreach ($options as $item) {
					if ($item['store_id'] == $store->getStoreId()) {
						$values[$store->getStoreId()][$item['fee_option_id']] = $this->htmlEscape($item['title']);
					}
				}
				if (!isset($values[$store->getStoreId()])) {
					$values[$store->getStoreId()] = array();
				}
			}
		}
        return $values;
    }

    public function getPriceTypeOfForm($optionId = 0)
    {
		return $this->getLayout()->createBlock('core/html_select')
			->setData(array('id' => 'option_price_type_'.$optionId, 'class' => 'multifees-input-full'))
			->setName("option[price_type][{$optionId}]")
			->setOptions($this->_getHelper()->setFieldOfForm($this->_getHelper()->getPriceTypeArray()))
			->getHtml();
    }
}