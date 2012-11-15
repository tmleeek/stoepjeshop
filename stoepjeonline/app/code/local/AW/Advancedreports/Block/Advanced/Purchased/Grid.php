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
 */?>
<?php
/**
 * Products by Customer Report Grid
 */
class AW_Advancedreports_Block_Advanced_Purchased_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    /**
     * Route to extract config from helper
     * @var string
     */
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_PURCHASED;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( Mage::helper('advancedreports')->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridPurchased');
    }

    /**
     * Has records to build report
     * @return boolean
     */
    public function hasRecords()
    {
        return false;
        return (count( $this->_customData ))
               && Mage::helper('advancedreports')->getChartParams( $this->_routeOption )
               && count( Mage::helper('advancedreports')->getChartParams( $this->_routeOption ) );
    }    


    /**
     * Add order query to collection select
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    public function addOrderItemsCount()
    {
        $itemTable = $this->getTable('sales_flat_order_item');

        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)", array( 'sum_qty' => 'SUM(item.qty_ordered)'))
                    ->where("e.entity_id = item.order_id")
                    ->group('e.entity_id')
                    ;                        
        } elseif (Mage::helper('advancedreports')->checkVersion('1.4.1.0')){
            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)", array( 'sum_qty' => 'SUM(item.qty_ordered)'))
                    ->where("main_table.entity_id = item.order_id")
                    ->group('main_table.entity_id')
                    ;
        } else {
            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)", array( 'sum_qty' => 'SUM(item.qty_ordered)'))
                    ->where("e.entity_id = item.order_id")
                    ->group('e.entity_id')
                    ;
        }

        $this->getCollection()->getSelect()
                ->columns(array(
                        'x_base_total' => 'base_grand_total',
                        'x_base_total_invoiced' => 'base_total_invoiced',
                        'x_base_total_refunded' => 'base_total_refunded',
                ));

        return $this;

    }

    /**
     * Prepare collection of report
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->addOrderItemsCount();
        $this->_helper()->setNeedMainTableAlias(true);
        $this->_prepareData();
        return $this;
    }

    /**
     * Add data to Data cache
     * @param array $row Row of data
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _addCustomData($row)
    {
        if ( count( $this->_customData ) )
        {
            foreach ( $this->_customData as &$d )
            {
                if ( isset( $d['customers'] ) && ($d['sum_qty'] == $row['sum_qty']) )
                {
                    $customers = $d['customers'];
                    unset($d['customers']);
                    $d['customers'] = $customers + 1;
                    $d['x_base_total'] = $d['x_base_total'] + $row['x_base_total'];
                    $d['x_base_total_invoiced'] = $d['x_base_total_invoiced'] + $row['x_base_total_invoiced'];
                    $d['x_base_total_refunded'] = $d['x_base_total_refunded'] + $row['x_base_total_refunded'];
                    return $this;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    /**
     * Prepare data array for Pie and Grid
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _prepareData()           
    {
//        echo $this->getCollection()->getSelect()->__toString();
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
            $row['customers'] = 1;
            $row['title'] = round($row['sum_qty']);
            $this->_addCustomData($row);
        }

//        var_dump( $this->_customData );

        if ( ! count( $this->_customData ) )
        {
            return $this;
        }

        usort($this->_customData, array(&$this, "_compareQtyElements") );
        Mage::helper('advancedreports')->setChartData( $this->_customData, Mage::helper('advancedreports')->getDataKey( $this->_routeOption ) );
        parent::_prepareData();
        return $this;
    }

    /**
     * Sort bestsellers values in two arrays
     * @param array $a
     * @param array $b
     * @return integer
     */
    protected function _compareQtyElements($a, $b)
    {
        if ($a['sum_qty'] == $b['sum_qty'])
        {
            return 0;
        }
        return ($a['sum_qty'] > $b['sum_qty']) ? -1 : 1;
    }

    /**
     * Prepare columns to show grid
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _prepareColumns()
    {
        $currency_code = $this->getCurrentCurrencyCode();

        $this->addColumn('sum_qty', array(
            'header'    =>Mage::helper('advancedreports')->__('Products Purchased'),
            'align'     =>'right',
            'index'     =>'sum_qty',
            'total'     =>'sum',
            'type'      =>'number'
        ));
                
        $this->addColumn('customers', array(
            'header'    =>Mage::helper('advancedreports')->__('Number of Customers'),
            'align'     =>'right',
            'index'     =>'customers',
            'total'     =>'sum',
            'type'      =>'number'
        ));

        $this->addColumn('x_base_total', array(
            'header'    =>Mage::helper('reports')->__('Total'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'x_base_total',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('x_base_total_invoiced', array(
            'header'    =>Mage::helper('reports')->__('Invoiced'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'x_base_total_invoiced',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('x_base_total_refunded', array(
            'header'    =>Mage::helper('reports')->__('Refunded'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'x_base_total_refunded',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));
        
        $this->addExportType('*/*/exportOrderedCsv', Mage::helper('advancedreports')->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', Mage::helper('advancedreports')->__('Excel'));

        return $this;
    }

    /**
     * Retrives type of chart for grid
     * (need for compatibiliy wit other reports)
     * @return string
     */
    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_PIE3D;
    }
    
}