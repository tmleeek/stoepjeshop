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

class MageWorx_Adminhtml_Block_Multifees_Fee_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('multifeesFeeGrid');
		$this->setDefaultSort('title');
        $this->setDefaultDir(Varien_Data_Collection::SORT_ORDER_ASC);
//		$this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
		$collection = Mage::getModel('multifees/fee')->getCollection();
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);

		return parent::_prepareCollection();
    }

	protected function _prepareColumns()
	{
		$this->addColumn('title', array(
		    'header'    => Mage::helper('multifees')->__('Title'),
		    'index'     => 'title',
			'filter'    => false,
		));

		$this->addColumn('input_type', array(
		    'header'    => Mage::helper('multifees')->__('Input Type'),
			'width'     => 150,
		    'index'     => 'input_type',
			'type'      => 'options',
		    'options'   => Mage::helper('multifees')->getTypeArray(true),
		));

		if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('multifees')->__('Store View'),
            	'width'         => 200,
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

		$this->addColumn('required', array(
		    'header'    => Mage::helper('multifees')->__('Is Required'),
		    'width'     => 80,
		    'index'     => 'required',
			'sortable'  => false,
			'type'      => 'options',
		    'options'   => Mage::helper('multifees')->getRequiredArray(),
		));

		$this->addColumn('sort_order', array(
		    'header'    => Mage::helper('multifees')->__('Sort Order'),
			'width'     => 80,
		    'index'     => 'sort_order',
			'filter'    => false,
		));

		$this->addColumn('status', array(
		    'header'    => Mage::helper('multifees')->__('Status'),
		    'width'     => 80,
		    'index'     => 'status',
		    'type'      => 'options',
		    'options'   => Mage::helper('multifees')->getStatusArray(),
		));

		$this->addColumn('action', array(
            'header'    => Mage::helper('multifees')->__('Action'),
            'width'     => 50,
            'align'     => 'center',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'mageworx/multifees_fee_grid_renderer_action'
        ));

		return parent::_prepareColumns();
	}

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('fee_id');
        $this->getMassactionBlock()->setFormFieldName('fee');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('multifees')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('multifees')->__('Are you sure you want to do this?')
        ));

        $statuses = Mage::helper('multifees')->getStatusArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));

        $this->getMassactionBlock()->addItem('status', array(
			'label'      => Mage::helper('multifees')->__('Change status'),
			'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
			'additional' => array(
				'visibility' => array(
					'name'   => 'status',
					'type'   => 'select',
					'class'  => 'required-entry',
					'label'  => Mage::helper('multifees')->__('Status'),
					'values' => $statuses
				)
			)
		));

        return $this;
    }

    public function getRowUrl($row)
    {
      return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'checkout' => $row->getCheckoutType()));
    }
}