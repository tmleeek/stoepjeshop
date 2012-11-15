<?php

class Ophirah_Qquoteadv_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        //$this->view->baseUrl = $this->_request->getBaseUrl();
        $this->setTemplate('qquoteadv/productlist.phtml');
     	$this->setColumn("test");
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_new_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('qquoteadv')->__('Add Selected Product(s) to Advanced Quote'),
                    'onclick' => "transfer_items()",
                    'class'   => 'add'
                    ))
                );
        /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                $this->getLayout()->createBlock('adminhtml/store_switcher')
                    ->setUseConfirm(false)
                    ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
            );
        }
        $this->setChild('grid', $this->getLayout()->createBlock('qquoteadv/adminhtml_product_grid', 'product.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_new_button');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

}

