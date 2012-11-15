<?php

class Devinc_Dailydeal_Block_Adminhtml_Dailydeal_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('dailydealGrid');
      $this->setDefaultSort('display_on');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('dailydeal/dailydeal')->getCollection()->setOrder('display_on', 'DESC')->setOrder('dailydeal_id', 'DESC');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('dailydeal_id', array(
          'header'    => Mage::helper('dailydeal')->__('Queue ID'),
          'align'     =>'left',
          'width'     => '30px',
          'index'     => 'dailydeal_id',
      ));

	  $productCollection = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect('entity_id')
				->addAttributeToSelect('name')
				->addAttributeToSelect('sku')
                ->load();
	
      $products = array();
      $products_sku = array();
            
	  $products[] = 'No product assigned';   
	  $products_sku[] = 'No product assigned';   
			
	  foreach ($productCollection as $product) {
          $products[$product->entity_id] = $product->name;          
          $products_sku[$product->entity_id] = $product->sku;          
      }		
		
	  $this->addColumn('product_name', array( 
          'header'    => Mage::helper('dailydeal')->__('Product name'),
          'align'     => 'left',
          'width'     => '150px',
          'index'     => 'product_id',
          'type'      => 'options',
          'options'   => $products
      ));   
	  
		
	  $this->addColumn('product_sku', array( 
          'header'    => Mage::helper('dailydeal')->__('Product SKU'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'product_id',
          'type'      => 'options',
          'options'   => $products_sku
      ));   
      
	  $this->addColumn('display_on', array(
          'header'    => Mage::helper('dailydeal')->__('Scheduled on'),
          'align'     => 'left',
          'width'     => '100px',
          'type'      => 'date',
          'default'   => '--',
          'index'     => 'display_on',
      ));	
	  
	  /* $this->addColumn('display_from', array(
          'header'    => Mage::helper('dailydeal')->__('On display from'),
          'align'     => 'left',
          'width'     => '100px',
          'type'      => 'datetime',
          'default'   => '--',
          'index'     => 'display_from',
      ));	  
      
	  $this->addColumn('display_to', array(
          'header'    => Mage::helper('dailydeal')->__('On display to'),
          'align'     => 'left',
          'width'     => '100px',
          'type'      => 'datetime',
          'default'   => '--',
          'index'     => 'display_to',
      )); */

      $this->addColumn('qty_sold', array(
          'header'    => Mage::helper('dailydeal')->__('Qty Sold'),
          'align'     =>'left',
          'width'     => '30px',
          'index'     => 'qty_sold',
      ));

      $this->addColumn('nr_views', array(
          'header'    => Mage::helper('dailydeal')->__('Nr. Views'),
          'align'     =>'left',
          'width'     => '30px',
          'index'     => 'nr_views',
      ));
	  
	  /* $this->addColumn('disable', array(
          'header'    => Mage::helper('dailydeal')->__('Disable product after deal ends'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'disable',
          'type'      => 'options',
          'options'   => array(
              1 => 'No',
              2 => 'Yes',
          ),
      )); */
	  
      $this->addColumn('status', array(
          'header'    => Mage::helper('dailydeal')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Queued',
              2 => 'Disabled',
              3 => 'Running',
          ),
          'renderer'  => 'dailydeal/adminhtml_dailydeal_grid_renderer_status',
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('dailydeal')->__('Action'),
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('dailydeal')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),		
                    array(
                        'caption'   => Mage::helper('dailydeal')->__('Delete'),
                        'url'       => array('base'=> '*/*/delete'),
                        'field'     => 'id',
						'confirm'  => Mage::helper('dailydeal')->__('Are you sure you want to delete this deal?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('dailydeal')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('dailydeal')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('dailydeal_id');
        $this->getMassactionBlock()->setFormFieldName('dailydeal');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('dailydeal')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('dailydeal')->__('Are you sure you want to delete these deals?')
        ));

        $statuses = Mage::getSingleton('dailydeal/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('dailydeal')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('dailydeal')->__('Status'),
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