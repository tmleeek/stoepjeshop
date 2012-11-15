<?php
/**
 * Faqs extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php

 * @category   FME
 * @package    Faqs
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Faqs_Block_Adminhtml_Faqs_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('faqsGrid');
      $this->setDefaultSort('faqs_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
   
      $collection = Mage::getModel('faqs/faqs')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $resource = Mage::getSingleton('core/resource');
	$read= $resource->getConnection('core_read');
	$topicTable = $resource->getTableName('faqs_topics');
	
	$select = $read->select()
	->from($topicTable,array('title', 'topic_id'))
	->order('topic_id ASC') ;
	$topics = $read->fetchAll($select);
	
	$topicwid=array();	
	for($i=0; $i<count($topics); $i++){
	  $topicwid[$topics[$i]['topic_id']]=  $topics[$i]['title'];
	}
      $this->addColumn('faqs_id', array(
          'header'    => Mage::helper('faqs')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'faqs_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('faqs')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
      
      $this->addColumn('topic_id', array(
          'header'    => Mage::helper('faqs')->__('Topic'),
          'align'     =>'left',
	  	  'width'     => '200px',
          'index'     => 'topic_id',
	 	  'type'      => 'options',
          'options'   => $topicwid,
      ));
	  
      $this->addColumn('status', array(
          'header'    => Mage::helper('faqs')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('faqs')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('faqs')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('faqs')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('faqs')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('faqs_id');
        $this->getMassactionBlock()->setFormFieldName('faqs');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faqs')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faqs')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faqs/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('faqs')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('faqs')->__('Status'),
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