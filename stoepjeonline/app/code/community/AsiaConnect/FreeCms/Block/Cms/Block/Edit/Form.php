<?php
class AsiaConnect_FreeCms_Block_Cms_Block_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('block_form');
        $this->setTitle(Mage::helper('cms')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('cms_block');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $form->setHtmlIdPrefix('block_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('cms')->__('General Information'), 'class' => 'fieldset-wide'));

        if ($model->getBlockId()) {
        	$fieldset->addField('block_id', 'hidden', array(
                'name' => 'block_id',
            ));
        }

    	$fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('cms')->__('Block Title'),
            'title'     => Mage::helper('cms')->__('Block Title'),
            'required'  => true,
        ));

    	$fieldset->addField('identifier', 'select', array(
            'name'      => 'identifier',
            'label'     => Mage::helper('cms')->__('Position'),
            'title'     => Mage::helper('cms')->__('Position'),
            'required'  => true,
            'class'     => 'validate-xml-identifier',
			'options'   => array(
                'sidebar-right-top' => Mage::helper('freecms')->__('Sidebar Right Top'),
                'sidebar-right-bottom' => Mage::helper('freecms')->__('Sidebar Right Bottom'),
                'sidebar-left-top' => Mage::helper('freecms')->__('Sidebar Left Top'),
                'sidebar-left-bottom' => Mage::helper('freecms')->__('Sidebar Left Bottom'),
                'content-top' => Mage::helper('freecms')->__('Content Top'),
                'menu-top' => Mage::helper('freecms')->__('Menu Top'),
                'menu-bottom' => Mage::helper('freecms')->__('Menu Bottom'),
                'page-bottom' => Mage::helper('freecms')->__('Page Bottom'),
                'catalog-sidebar-right-top' => Mage::helper('freecms')->__('Only Catalog Sidebar Right Top'),
                'catalog-sidebar-right-bottom' => Mage::helper('freecms')->__('Only Catalog Sidebar Right Bottom'),
                'catalog-sidebar-left-top' => Mage::helper('freecms')->__('Only Catalog Sidebar Left Top'),
                'catalog-sidebar-left-bottom' => Mage::helper('freecms')->__('Only Catalog Sidebar Left Bottom'),
                'catalog-content-top' => Mage::helper('freecms')->__('Only Catalog Content Top'),
                'catalog-menu-top' => Mage::helper('freecms')->__('Only Catalog Menu Top'),
                'catalog-menu-bottom' => Mage::helper('freecms')->__('Only Catalog Menu Bottom'),
                'catalog-page-bottom' => Mage::helper('freecms')->__('Only Catalog Page Bottom'),
                'product-sidebar-right-top' => Mage::helper('freecms')->__('Only Product Sidebar Right Top'),
                'product-sidebar-right-bottom' => Mage::helper('freecms')->__('Only Product Sidebar Right Bottom'),
                'product-sidebar-left-top' => Mage::helper('freecms')->__('Only Product Sidebar Left Top'),
                'product-content-top' => Mage::helper('freecms')->__('Only Product Content Top'),
                'product-menu-top' => Mage::helper('freecms')->__('Only Product Menu Top'),
                'product-menu-bottom' => Mage::helper('freecms')->__('Only Product Menu Bottom'),
                'product-page-bottom' => Mage::helper('freecms')->__('Only Product Page Bottom'),
                'product-sidebar-left-bottom' => Mage::helper('freecms')->__('Only Product Sidebar Left Bottom'),
                'catalog-menu-bottom' => Mage::helper('freecms')->__('Only Customer Content Top'),
                'cart-content-top' => Mage::helper('freecms')->__('Only Cart Content Top'),
            ),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
        	$fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

    	$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('cms')->__('Status'),
            'title'     => Mage::helper('cms')->__('Status'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('cms')->__('Enabled'),
                '0' => Mage::helper('cms')->__('Disabled'),
            ),
        ));

    	$fieldset->addField('content', 'editor', array(
            'name'      => 'content',
            'label'     => Mage::helper('cms')->__('Content'),
            'title'     => Mage::helper('cms')->__('Content'),
            'style'     => 'height:36em',
            'wysiwyg'   => false,
            'required'  => true,
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
