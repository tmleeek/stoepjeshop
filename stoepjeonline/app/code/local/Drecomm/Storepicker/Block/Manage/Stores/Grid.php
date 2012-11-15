<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-L.txt
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-L.txt
 */

class Drecomm_Storepicker_Block_Manage_Stores_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('storesGrid');
		$this->setDefaultSort('created_time');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('storepicker/store')->getCollection();
		$store = $this->_getStore();
		if ($store->getId()) {
            $collection->addStoreFilter($store);
		}
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('store_id', array(
		  'header'    => Mage::helper('storepicker')->__('ID'),
		  'align'     =>'right',
		  'width'     => '50px',
		  'index'     => 'id',
		));

		$this->addColumn('customerno', array(
		  'header'    => Mage::helper('storepicker')->__('Customer No.'),
		  'align'     =>'left',
		  'width'     => '50px',
		  'index'     => 'customerno',
		));

		$this->addColumn('name', array(
		  'header'    => Mage::helper('storepicker')->__('Name'),
		  'align'     =>'left',
		  'index'     => 'name',
		));
		
		$this->addColumn('street', array(
		  'header'    => Mage::helper('storepicker')->__('Street'),
		  'align'     => 'left',
		  'index'     => 'street',
		));
		
		$this->addColumn('housenr', array(
			'header'    => Mage::helper('storepicker')->__('Housenr'),
			'width'     => '150px',
			'index'     => 'housenr',
		));
		
		
		$this->addColumn('postal', array(
			'header'    => Mage::helper('storepicker')->__('Postal'),
			'align'     => 'left',
			'width'     => '120px',
			'index'     => 'postal',
		));
		
		$this->addColumn('city', array(
			'header'    => Mage::helper('storepicker')->__('City'),
			'align'     => 'left',
			'width'     => '120px',
			'index'     => 'city',
		));
		
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('storepicker')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('storepicker')->__('Edit'),
						'url'       => array('base'=> '*/*/edit'),
						'field'     => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
		
		return parent::_prepareColumns();
	}

    protected function __prepareMassaction()
    {
        $this->setMassactionIdField('post_id');
        $this->getMassactionBlock()->setFormFieldName('storepicker');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('storepicker')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('storepicker')->__('Are you sure?')
        ));

        //$statuses = Mage::getSingleton('storepicker/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('storepicker')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('storepicker')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}
