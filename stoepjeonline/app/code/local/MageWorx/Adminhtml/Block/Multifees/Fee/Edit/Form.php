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

class MageWorx_Adminhtml_Block_Multifees_Fee_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	private $_id;

	public function __construct()
	{
		parent::__construct();
        $this->setTemplate('multifees/form.phtml');
        $this->_id = (int) $this->getRequest()->getParam('id');

        $this->initForm();
	}

	protected function initForm()
	{
		$form = new Varien_Data_Form(array(
			'id'      => 'edit_form',
			'action'  => $this->getUrl('*/*/save', array('id' => $this->_id)),
			'method'  => 'post',
			)
	    );

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

        $form->addField('required', 'select', array(
		    'name'      => 'fee[required]',
			'class'     => 'multifees-input-full',
		    'values'    => Mage::helper('multifees')->setFieldOfForm(Mage::helper('multifees')->getRequiredArray()),
		));

		$form->addField('input_type', 'select', array(
		    'name'      => 'fee[input_type]',
			'class'     => 'multifees-input-middle',
		    'onchange'  => 'attributeOption.checkInType(this)',
		    'required'  => true,
		    'values'    => Mage::helper('multifees')->setFieldOfForm(Mage::helper('multifees')->getTypeArray(), true),
		));

		$form->addField('sort_order', 'text', array(
		    'name'      => 'fee[sort_order]',
		    'class'     => 'validate-zero-or-greater',
		));

		$form->addField('status', 'select', array(
		    'name'      => 'fee[status]',
			'class'     => 'multifees-input-full',
		    'values'    => Mage::helper('multifees')->setFieldOfForm(Mage::helper('multifees')->getStatusArray()),
		));

        $form->addField('select_apply_to', 'select', array(
            'onchange' => 'toggleApplyVisibility(this)',
            'values'      => array(
                    'all'     => Mage::helper('catalog')->__('All Product Types'),
                    'custom'  => Mage::helper('catalog')->__('Selectable Product Type')
            ),
            'required'    => true
            ), 'frontend_class');

        $form->addField('apply_to', 'multiselect', array(
                'name'        => 'fee[apply_to]',
                'values'      => Mage_Catalog_Model_Product_Type::getOptions(),
                'class'     => 'multifees-input-long',
                'required'    => true
        ));

        $form->getElement('apply_to')->setSize(5);

        $form->addField('custom', 'hidden', array(
                'name'      => 'custom',
                'values'     => 0,
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
                $formData['apply_to'] = $this->_getApplyTo($formData);
                if (count($formData['apply_to']) == 1 && '' == current($formData['apply_to'])) {
                    $formData['custom'] = 0;
                } else {
                    $formData['custom'] = 1;
                }
            } else {
                $formData = $model->getData();
            }

            $form->setValues($formData);
        }
        $this->setForm($form);

        return $this;
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

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('multifees')->__('Delete'),
                    'class'   => 'delete delete-option'
                )));

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('multifees')->__('Add Option'),
                    'class'   => 'add',
                    'id'      => 'add_new_option_button'
                )));

		$this->setChild('add_image_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('multifees')->__('Add Image'),
                    'class'   => 'add',
                    'id'      => 'new-option-file-{{id}}',
                	'onclick' => 'attributeOption.createFileField(this.id)'
                )));

        return parent::_prepareLayout();
    }

	public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

	public function getAddImageButtonHtml()
    {
        return $this->getChildHtml('add_image_button');
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
		$values = array();
		if ($this->_id) {
			$collection = Mage::getResourceModel('multifees/option_collection')
				->addFeeFilter($this->_id)
				->addPositionOrder()
				->getItems();

			foreach ($collection as $option) {
                $value = array();
                $isDefault = $option->getIsDefault();
                if (!empty($isDefault)) {
                    $value['checked'] = 'checked="checked"';
                } else {
                    $value['checked'] = '';
                }
                $value['id']         = $option->getId();
                $value['price']      = Mage::app()->getStore()->roundPrice($option->getPrice());
                $value['price_type'] = $option->getPriceType();
                $value['sort_order'] = $option->getPosition();

				if (Mage::helper('multifees')->isMultifeesFile($option->getId())) {
					$impOption = array(
						'label' => Mage::helper('multifees')->__('Delete Image'),
						'url'   => $this->getUrl('*/*/getImage', array('option' => $option->getId())),
						'id'    => $option->getId()
					);
					$value['image'] = Mage::helper('multifees')->getOptionImgView(new Varien_Object($impOption));
				}

                $stores = $this->getStoreOptionValues($option->getOption());
                foreach ($this->getStores() as $store) {
                    $storeValues = $stores[$store->getStoreId()];
                    if (isset($storeValues[$option->getId()])) {
                        $value['store'. $store->getStoreId()] = $storeValues[$option->getId()];
                    } else {
                        $value['store'. $store->getStoreId()] = '';
                    }
                }
                $values[] = new Varien_Object($value);
            }
    	}
        return $values;
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

    public function getPriceTypeOfForm()
    {
		return $this->getLayout()->createBlock('core/html_select')
			->setData(array(
				'id'    => 'option_price_type_{{id}}',
				'class' => 'multifees-input-full'))
			->setName('option[price_type][{{id}}]')
			->setOptions(Mage::helper('multifees')->setFieldOfForm(Mage::helper('multifees')->getPriceTypeArray()))
			->getHtml();
    }

    protected function _getApplyTo($formData) {
        if ($formData) {
            if (is_array($formData['apply_to'])) {
                return $formData['apply_to'];
            }
            return explode(',', $formData['apply_to']);
        } else {
            return array();
        }
    }
}