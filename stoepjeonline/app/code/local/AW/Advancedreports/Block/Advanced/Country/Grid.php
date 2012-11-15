<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
/**
 * Country Report Grid
 */
class AW_Advancedreports_Block_Advanced_Country_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    /**
     * Route to helper options
     * @var string
     */
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_COUNTRY;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( Mage::helper('advancedreports')->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridCountry');
    }

    /**
     * Retrives Show Chart flag
     * @return boolean
     */
    public function hasRecords()
    {
        return (count( $this->_customData ))
               && Mage::helper('advancedreports')->getChartParams( $this->_routeOption )
               && count( Mage::helper('advancedreports')->getChartParams( $this->_routeOption ) );
    }

    /**
     * Add items to select request
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    public  function addOrderItemsCount()
    {
        $itemTable = $this->getTable('sales_flat_order_item');

        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)",
                                array(
                                        'sum_qty' => 'SUM(item.qty_ordered)',
                                        'sum_total' => 'SUM(item.base_row_total)',
                                    ))
                    ->where("e.entity_id = item.order_id"); 


        } elseif (Mage::helper('advancedreports')->checkVersion('1.4.1.0')){
            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)",
                                array(
                                        'sum_qty' => 'SUM(item.qty_ordered)',
                                        'sum_total' => 'SUM(item.base_row_total)',
                                    ))
                    ->where("main_table.entity_id = item.order_id");
        } else {
            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)",
                                array(
                                        'sum_qty' => 'SUM(item.qty_ordered)',
                                        'sum_total' => 'SUM(item.base_row_total)',
                                    ))
                    ->where("e.entity_id = item.order_id");
        }
        return $this;
    }

    /**
     * Retrives array with target values for Join Address Data to collection
     * @param array $values
     * @param string $addressType
     * @return array
     */
    protected function _prepareValuesArray($values, $addressType)
    {
        $result = array($addressType.'_flag'=>"IF((`flat_quote_addr_{$addressType}`.`email` IS NOT NULL), '1', '0')");
        if (count($values)){
            foreach($values as $value){
                $result[$addressType.'_'.$value] = $value;
            }
        }
        return $result;
    }

    /**
     * Add address data to Report Collection
     * @param Mage_Sales_Model_Mysql4_Order_Collection $collection
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    public function addAddress()
    {
        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){

            # 1.7.1.0 Only
            $entityValue = $this->getTable('sales_order_entity_varchar');
            $entityAtribute = $this->getTable('eav_attribute');
            $entityType = $this->getTable('eav_entity_type');
            $orderEntity = $this->getTable('sales_order_entity');

            $this->getCollection()->getSelect()
                ->joinLeft(array('_eavType'=>$entityType), "_eavType.entity_type_code = 'order_address'", array())                
                ->joinLeft(array('_addrTypeAttr'=>$entityAtribute), "_addrTypeAttr.entity_type_id = _eavType.entity_type_id AND _addrTypeAttr.attribute_code = 'address_type'", array())
                ->joinLeft(array('_addrValueAttr'=>$entityAtribute), "_addrValueAttr.entity_type_id = _eavType.entity_type_id AND _addrValueAttr.attribute_code = 'country_id'", array())

                # Shipping values
                ->joinRight(array('_orderEntity_ship'=>$orderEntity), "_orderEntity_ship.entity_type_id = _eavType.entity_type_id AND _orderEntity_ship.parent_id = e.entity_id", array())
                ->joinRight(array('_addrTypeVal_ship'=>$entityValue), "_addrTypeVal_ship.attribute_id = _addrTypeAttr.attribute_id AND _addrTypeVal_ship.entity_id = _orderEntity_ship.entity_id AND _addrTypeVal_ship.value = 'shipping'", array())
                ->joinRight(array('_addrCountryVal_ship'=>$entityValue), "_addrCountryVal_ship.attribute_id = _addrValueAttr.attribute_id AND _addrCountryVal_ship.entity_id = _orderEntity_ship.entity_id", array())

                # Billing values
                ->joinRight(array('_orderEntity_bill'=>$orderEntity), "_orderEntity_bill.entity_type_id = _eavType.entity_type_id AND _orderEntity_bill.parent_id = e.entity_id", array())
                ->joinRight(array('_addrTypeVal_bill'=>$entityValue), "_addrTypeVal_bill.attribute_id = _addrTypeAttr.attribute_id AND _addrTypeVal_bill.entity_id = _orderEntity_bill.entity_id AND _addrTypeVal_bill.value = 'billing'", array())
                ->joinRight(array('_addrCountryVal_bill'=>$entityValue), "_addrCountryVal_bill.attribute_id = _addrValueAttr.attribute_id AND _addrCountryVal_bill.entity_id = _orderEntity_bill.entity_id", array())

                ->columns(array('country_id' => 'IFNULL(_addrCountryVal_ship.value, _addrCountryVal_bill.value)'))
                ->group('country_id')
            ;

        } elseif ($this->_checkVer('1.4.1.0')){
            # Community after 1.4.1.0 and Enterprise
            $salesFlatOrderAddress = $this->getTable('sales_flat_order_address');
            $this->getCollection()->getSelect()
                ->joinLeft(array('flat_order_addr_ship'=>$salesFlatOrderAddress), "flat_order_addr_ship.parent_id = main_table.entity_id AND flat_order_addr_ship.address_type = 'shipping'", array())
                ->joinLeft(array('flat_order_addr_bill'=>$salesFlatOrderAddress), "flat_order_addr_bill.parent_id = main_table.entity_id AND flat_order_addr_bill.address_type = 'billing'", array())
                ->columns(array('country_id' => 'IFNULL(flat_order_addr_ship.country_id, flat_order_addr_bill.country_id)'))
                ->group('country_id')
            ;            
        } else {
            # Old Community
            $entityValue = $this->getTable('sales_order_entity_varchar');
            $entityAtribute = $this->getTable('eav_attribute');
            $entityType = $this->getTable('eav_entity_type');
            $orderEntity = $this->getTable('sales_order_entity');

            $this->getCollection()->getSelect()
                ->joinLeft(array('_eavType'=>$entityType), "_eavType.entity_type_code = 'order_address'", array())
                ->joinLeft(array('_addrTypeAttr'=>$entityAtribute), "_addrTypeAttr.entity_type_id = _eavType.entity_type_id AND _addrTypeAttr.attribute_code = 'address_type'", array())
                ->joinLeft(array('_addrValueAttr'=>$entityAtribute), "_addrValueAttr.entity_type_id = _eavType.entity_type_id AND _addrValueAttr.attribute_code = 'country_id'", array())

                # Shipping values
                ->joinRight(array('_orderEntity_ship'=>$orderEntity), "_orderEntity_ship.entity_type_id = _eavType.entity_type_id AND _orderEntity_ship.parent_id = e.entity_id", array())
                ->joinRight(array('_addrTypeVal_ship'=>$entityValue), "_addrTypeVal_ship.attribute_id = _addrTypeAttr.attribute_id AND _addrTypeVal_ship.entity_id = _orderEntity_ship.entity_id AND _addrTypeVal_ship.value = 'shipping'", array())
                ->joinRight(array('_addrCountryVal_ship'=>$entityValue), "_addrCountryVal_ship.attribute_id = _addrValueAttr.attribute_id AND _addrCountryVal_ship.entity_id = _orderEntity_ship.entity_id", array())

                # Billing values
                ->joinRight(array('_orderEntity_bill'=>$orderEntity), "_orderEntity_bill.entity_type_id = _eavType.entity_type_id AND _orderEntity_bill.parent_id = e.entity_id", array())
                ->joinRight(array('_addrTypeVal_bill'=>$entityValue), "_addrTypeVal_bill.attribute_id = _addrTypeAttr.attribute_id AND _addrTypeVal_bill.entity_id = _orderEntity_bill.entity_id AND _addrTypeVal_bill.value = 'billing'", array())
                ->joinRight(array('_addrCountryVal_bill'=>$entityValue), "_addrCountryVal_bill.attribute_id = _addrValueAttr.attribute_id AND _addrCountryVal_bill.entity_id = _orderEntity_bill.entity_id", array())

                ->columns(array('country_id' => 'IFNULL(_addrCountryVal_ship.value, _addrCountryVal_bill.value)'))
                ->group('country_id')

            ;
        }
        return $this;
    }

    /**
     * Prepare report collection
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();    
        $this->addAddress();
        $this->addOrderItemsCount();                
        $this->_helper()->setNeedMainTableAlias(true);        
          $this->_prepareData();
        return $this;
    }

    /**
     * Add row to custom data
     * @param array $row
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    protected function _addCustomData($row)
    {
        if ( count( $this->_customData ) )
        {
            foreach ( $this->_customData as &$d )
            {
                if ( $d['country_id'] === $row['country_id'] )
                {
                    $qty = $d['qty_ordered'];
                    $total = $d['total'];
                    unset($d['total']);
                    unset($d['qty_ordered']);
                    $d['total'] = $row['total'] + $total;
                    $d['qty_ordered'] = $row['qty_ordered'] + $qty;
                    return $this;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    /**
     * Retrive compare result for two arrays by Total
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _compareTotalElements($a, $b)
    {
        if ($a['total'] == $b['total'])
        {
            return 0;
        }
        return ($a['total'] > $b['total']) ? -1 : 1;
    }

    /**
     * Retrive compare result for two arrays by Quantity element
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _compareQtyElements($a, $b)
    {
        if ($a['qty_ordered'] == $b['qty_ordered'])
        {
            return 0;
        }
        return ($a['qty_ordered'] > $b['qty_ordered']) ? -1 : 1;
    }

    /**
     * Prepare Custom Data to show chart
     * @return AW_Advancedreports_Block_Advanced_Country_Grid
     */
    protected function _prepareData()
    {
//        echo $this->getCollection()->getSelect()->__toString(); die;
        foreach ( $this->getCollection() as $order )
        {
            $row = array();
            foreach ($this->_columns as $column)
            {
                    if ( !$column->getIsSystem() )
                {
                    $row[ $column->getIndex() ] = $order->getData( $column->getIndex() );
                }
            }
            if ($order->getCountryId())
            {
                $row['country_id']  = $order->getCountryId();
                $row['qty_ordered'] = $order->getSumQty();
                $row['total']       = $order->getSumTotal();
                $this->_addCustomData($row);
            }
        }

        if ( ! count( $this->_customData ) )
        {
            return $this;
        }

        $key = $this->getFilter('reload_key');
        if ( $key === 'qty' )
        {
            //Sort data
            usort($this->_customData, array(&$this, "_compareQtyElements") );

            //All qty
            $qty = 0;
            foreach ( $this->_customData as $d )
            {
                $qty += $d['qty_ordered'];
            }
            foreach ( $this->_customData as $i=>&$d )
            {
                $d['order'] = $i + 1;
                $d['percent'] = round( $d['qty_ordered'] * 100 / $qty ).' %'; 
                $d['percent_data'] = round( $d['qty_ordered'] * 100 / $qty );
                //Add title
                $d['country_name'] = Mage::getSingleton('directory/country')->loadByCode( $d['country_id'] )->getName();;
            }
        }
        elseif ($key === 'total')
        {
            //Sort data
            usort($this->_customData, array(&$this, "_compareTotalElements") );

            //All qty
            $total = 0;
            foreach ( $this->_customData as $d )
            {
                $total += $d['total'];
            }
            foreach ( $this->_customData as $i=>&$d )
            {
                $d['order'] = $i + 1;
                if ($total != 0){
                    $d['percent'] = round( $d['total'] * 100 / $total ).' %';
                    $d['percent_data'] = round( $d['total'] * 100 / $total );
                } else {
                    $d['percent'] = '0 %';
                    $d['percent_data'] = 0;
                }                
                
                //Add title
                $d['country_name'] = Mage::getSingleton('directory/country')->loadByCode( $d['country_id'] )->getName();;
            }
        }
        else
        {
            return $this;
        }

        Mage::helper('advancedreports')->setChartData( $this->_customData, Mage::helper('advancedreports')->getDataKey( $this->_routeOption ) );
        parent::_prepareData();
        return $this;
    }

    /**
     * Retrives flag to show params selector always
     * @return boolean
     */
    public function getShowAnyway()
    {
        return true;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order', array(
            'header'    =>Mage::helper('reports')->__('N'),
            'width'     =>'60px',
            'align'     =>'right',
            'index'     =>'order',
            'type'      =>'number'
        ));

        $this->addColumn('country_name', array(
            'header'    =>Mage::helper('reports')->__('Country'),
            'index'     =>'country_name'
        ));

        $this->addColumn('percent', array(
            'header'    =>Mage::helper('advancedreports')->__('Percent'),
            'width'     =>'60px',
            'align'     =>'right',
            'index'     =>'percent',
            'type'      =>'text'
        ));

        $this->addColumn('qty_ordered', array(
            'header'    =>Mage::helper('advancedreports')->__('Quantity'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'qty_ordered',
            'total'     =>'sum',
            'type'      =>'number'
        ));

        $this->addColumn('total', array(
            'header'    =>Mage::helper('reports')->__('Total'),
            'width'     =>'120px',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index' => 'total',
            'type'  => 'currency',

        ));

        $this->addExportType('*/*/exportOrderedCsv', Mage::helper('advancedreports')->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', Mage::helper('advancedreports')->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_MAP;
    }


}
