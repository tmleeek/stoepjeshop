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
 * Sales Report Grid
 */
class AW_Advancedreports_Block_Advanced_Sales_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    const OPTION_SALES_GROUPED_SKU = 'advancedreports_sales_options_skutype';

    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_SALES;
    protected $_optCollection;
    protected $_optCache = array();

    /**
     * Cache with addresses for orders
     * @var array
     */
    protected $_addresses = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( Mage::helper('advancedreports')->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setId('gridAdvancedSales');


        # Init aggregator
        $this->getAggregator()->initAggregator($this, AW_Advancedreports_Helper_Tools_Aggregator::TYPE_LIST, $this->getRoute(), $this->_helper()->confOrderDateFilter());

        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        if (isset($storeIds))
        {
            $this->getAggregator()->setStoreFilter($storeIds);
        }

    }

    public function getHideShowBy()
    {
        return true;
    }

    /**
     * Retrives initialization array for custom report option
     * @return array
     */
    public function  getCustomOptionsRequired()
    {
        $array = parent::getCustomOptionsRequired();

        $include = Mage::getModel('advancedreports/system_config_source_include');
        $skutypes = Mage::getSingleton('advancedreports/system_config_source_skutype')->toOptionArray();
        $addArray = array(
            array(
                'id'=>'include_refunded',
                'type'=> 'select',
                'args' => array(
                    'label'     => Mage::helper('advancedreports')->__('Include refunded items'),
                    'title'     => Mage::helper('advancedreports')->__('Include refunded items'),
                    'name'      => 'include_refunded',
                    'values'    => $include->toOptionArray(),
                ),
                'default' => '1'
            ),
            array(
                'id'=>self::OPTION_SALES_GROUPED_SKU,
                'type'=> 'select',
                'args' => array(
                    'label'     => Mage::helper('advancedreports')->__('SKU usage'),
                    'title'     => Mage::helper('advancedreports')->__('SKU usage'),
                    'name'      => self::OPTION_SALES_GROUPED_SKU,
                    'class'     => '',
                    'required'  => true,
                    'values'    => $skutypes
                ),
                'default' => AW_Advancedreports_Model_System_Config_Source_Skutype::SKUTYPE_SIMPLE
            ),

        );
        return array_merge($array, $addArray);
    }


    protected function _addCustomData($row)
    {
//        if ($this->_filterPass($row)){
            $this->_customData[] = $row;
//        }        
        return $this;
    }

    public function addOrderItems()
    {
        $skuType = $this->getCustomOption(self::OPTION_SALES_GROUPED_SKU);
        $productTable = $this->getTable('catalog_product_entity');

        $filterField = Mage::helper('advancedreports')->confOrderDateFilter();
        $itemTable = $this->getTable('sales_flat_order_item');
        $orderTable = $this->getTable('sales_order');
        $notSimple = "'configurable','bundle'";


        if ($skuType == AW_Advancedreports_Model_System_Config_Source_Skutype::SKUTYPE_SIMPLE){

            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND ((item.parent_item_id IS NULL AND item.product_type NOT IN ({$notSimple})) OR ( item.parent_item_id IS NOT NULL  AND item.product_type NOT IN ({$notSimple})) ))" )               
                    ;

        } else {

            $this->getCollection()->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)" )                                       
                    ;
        }

        $this->getCollection()->getSelect()
                    ->joinLeft(array('item2'=>$itemTable), "(item.parent_item_id IS NOT NULL AND item.parent_item_id = item2.item_id AND item2.product_type = 'configurable')", array())
                    ->joinLeft(array('realP'=>$productTable), "item.product_id = realP.entity_id", array('real_sku'=>'realP.sku'))
                    ->order("e.{$filterField} DESC")
                    ;

        return $this;
    }
    
    protected function _addManufacturer($collection)
    {    
        $entityProduct = $this->getTable('catalog_product_entity');
        $entityValuesVarchar = $this->getTable('catalog_product_entity_varchar');
        $entityValuesInt = $this->getTable('catalog_product_entity_int');
        $entityAtribute = $this->getTable('eav_attribute');
        $eavAttrOptVal = $this->getTable('eav_attribute_option_value');
        $collection->getSelect()
            ->join( array( '_product'=>$entityProduct ), "_product.entity_id = item.product_id", array( 'p_product_id' => 'item.product_id' ) )
            ->joinLeft( array( '_manAttr'=>$entityAtribute ), "_manAttr.attribute_code = 'manufacturer'", array() )
            ->joinLeft( array( '_manValVarchar'=>$entityValuesVarchar ), "_manValVarchar.attribute_id = _manAttr.attribute_id AND _manValVarchar.entity_id = _product.entity_id", array() )
            ->joinLeft( array( '_manValInt'=>$entityValuesInt ), "_manValInt.attribute_id = _manAttr.attribute_id AND _manValInt.entity_id = _product.entity_id", array() )
            ->joinLeft( array( '_optVal'=>$eavAttrOptVal ), "_optVal.option_id = IFNULL(_manValInt.value, _manValVarchar.value) AND _optVal.store_id = 0", array('product_manufacturer'=>'value') )
        ;    
    }    
    
    protected function _addAddress($collection)
    {
        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
            
            # Do nothing. Getting this data by other request for EE 1.7.1.0

        } elseif (Mage::helper('advancedreports')->checkVersion('1.4.1.0')){
            $salesFlatOrderAddress = $this->getTable('sales_flat_order_address');
            $collection->getSelect()
                ->joinLeft(array('flat_order_addr_ship'=>$salesFlatOrderAddress), "flat_order_addr_ship.parent_id = e.entity_id AND flat_order_addr_ship.address_type = 'shipping'", array(
                        'order_ship_postcode' => 'postcode',
                        'order_ship_country_id' => 'country_id',
                        'order_ship_region' => 'region',
                        'order_ship_city' => 'city',
                        'order_ship_email' => 'email',
                    ))
                ->joinLeft(array('flat_order_addr_bil'=>$salesFlatOrderAddress), "flat_order_addr_bil.parent_id = e.entity_id AND flat_order_addr_bil.address_type = 'billing'", array(
                        'order_bil_postcode' => 'postcode',
                        'order_bil_country_id' => 'country_id',
                        'order_bil_region' => 'region',
                        'order_bil_city' => 'city',
                        'order_bil_email' => 'email',
                    ))
            ;
        } else {
            $entityValues = $this->getTable('sales_order_int');
            $entityAtribute = $this->getTable('eav_attribute');
            $entityType = $this->getTable('eav_entity_type');
            $salesFlatQuote = $this->getTable('sales_flat_quote');
            $salesFlatQuoteAddress = $this->getTable('sales_flat_quote_address');
            $collection->getSelect()
                ->joinLeft(array('a_type_order'=>$entityType), "a_type_order.entity_type_code='order'", array())
                ->joinLeft(array('a_attr_quote'=>$entityAtribute), "a_type_order.entity_type_id=a_attr_quote.entity_type_id AND a_attr_quote.attribute_code = 'quote_id'", array())
                ->joinLeft(array('a_value_quote'=>$entityValues), "a_value_quote.entity_id = e.entity_id AND a_value_quote.attribute_id = a_attr_quote.attribute_id", array())
                ->joinLeft(array('flat_quote'=>$salesFlatQuote), "flat_quote.entity_id = a_value_quote.value", array())
                ->joinLeft(array('flat_quote_addr_ship'=>$salesFlatQuoteAddress), "flat_quote_addr_ship.quote_id = flat_quote.entity_id AND flat_quote_addr_ship.address_type = 'shipping'", array(
                        'order_ship_postcode' => 'postcode',
                        'order_ship_country_id' => 'country_id',
                        'order_ship_region' => 'region',
                        'order_ship_city' => 'city',
                        'order_ship_email' => 'email',
                    ))
                ->joinLeft(array('flat_quote_addr_bil'=>$salesFlatQuoteAddress), "flat_quote_addr_bil.quote_id = flat_quote.entity_id AND flat_quote_addr_bil.address_type = 'billing'", array(
                        'order_bil_postcode' => 'postcode',
                        'order_bil_country_id' => 'country_id',
                        'order_bil_region' => 'region',
                        'order_bil_city' => 'city',
                        'order_bil_email' => 'email',
                    ))
            ;
        }
        return $this;        
    }    

    /**
     * Prepare array with collected data
     * 
     * @param datetime $from
     * @param datetime $to
     * @return array
     */
    public function getPreparedData($from, $to)
    {
        $this->setCollection( Mage::getModel('advancedreports/order')->getCollection() );

        $this->setDateFilter($from, $to)->setState();

        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        if (isset($storeIds))
        {
            $this->setStoreFilter($storeIds);
        }
        
        $filterField = Mage::helper('advancedreports')->confOrderDateFilter();
        $collection = $this->getCollection();

        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
            $orderTable = $this->getTable('sales_order');
        } elseif (Mage::helper('advancedreports')->checkVersion('1.4.1.0')){
            $orderTable = $this->getTable('sales_flat_order');
        } else {
            $orderTable = $this->getTable('sales_order');
        }

        $collection->getSelect()->reset();
        $collection->getSelect()->from(array('e'=>$orderTable), array(
            'order_created_at' => $filterField,
            'order_id' => 'entity_id',
            'order_increment_id' => 'increment_id',

        ));

        $collection->getSelect()
                    # sku
                    ->columns(array('xsku'=>"IFNULL(realP.sku, item.sku)"))

                    # price
                    ->columns(array('base_xprice'=>"IFNULL(item2.base_price, item.base_price)"))

                    # subtotal
                    ->columns(array('base_row_subtotal'=>"( IFNULL(item2.qty_ordered, item.qty_ordered) * IFNULL(item2.base_price, item.base_price) )"))

                    # total
                    ->columns(array('base_row_xtotal_incl_tax'=>"( IFNULL(item2.qty_ordered, item.qty_ordered) * IFNULL(item2.base_price, item.base_price) - ABS( IFNULL(item2.base_discount_amount,item.base_discount_amount) ) + IFNULL(item2.base_tax_amount, item.base_tax_amount) )"))
                    ->columns(array('base_row_xtotal'=>"( IFNULL(item2.qty_ordered, item.qty_ordered) * IFNULL(item2.base_price, item.base_price) - ABS( IFNULL( item2.base_discount_amount, item.base_discount_amount ) ) )"))

                    # invoiced
                    ->columns(array('base_row_xinvoiced'=>"( IFNULL(item2.qty_invoiced, item.qty_invoiced) * IFNULL(item2.base_price, item.base_price) - ABS(item.base_discount_amount)  )"))
                    ->columns(array('base_row_xinvoiced_incl_tax'=>"( IFNULL(item2.qty_invoiced, item.qty_invoiced) * IFNULL(item2.base_price, item.base_price) - ABS( IFNULL(item2.base_discount_amount, item.base_discount_amount) )  + IFNULL(item2.base_tax_invoiced, item.base_tax_invoiced) )"))

                    # refunded
                    ->columns(array('base_row_xrefunded'=>"( (IF((IFNULL(item2.qty_refunded, item.qty_refunded) > 0), 1, 0) * (  (IFNULL(item2.qty_refunded, item.qty_refunded) / IFNULL(item2.qty_invoiced, item.qty_invoiced)) * ( IFNULL(item2.qty_invoiced, item.qty_invoiced) * IFNULL(item2.base_price, item.base_price) - ABS( IFNULL(item2.base_discount_amount, item.base_discount_amount) ) )  ) ) )"))

                    ->columns(array('base_tax_xrefunded'=>"IF(( IFNULL(item2.qty_refunded, item.qty_refunded) > 0), ( IFNULL(item2.qty_refunded, item.qty_refunded) / IFNULL(item2.qty_invoiced, item.qty_invoiced) *  IFNULL(item2.base_tax_invoiced, item.base_tax_invoiced) ), 0)"))
                    ->columns(array('base_row_xrefunded_incl_tax'=>"((IF(( IFNULL(item2.qty_refunded, item.qty_refunded) > 0), 1, 0) * (  (IFNULL(item2.qty_refunded, item.qty_refunded)  * ( IFNULL(item2.qty_invoiced, item.qty_invoiced) * IFNULL(item2.base_price, item.base_price) - ABS( IFNULL(item2.base_discount_amount, item.base_discount_amount) ) ) / IFNULL(item2.qty_invoiced, item.qty_invoiced) ) + IF((IFNULL(item2.qty_refunded, item.qty_refunded) > 0) , ( IFNULL(item2.qty_refunded, item.qty_refunded) / IFNULL(item2.qty_invoiced, item.qty_invoiced)  *  IFNULL(item2.base_tax_invoiced, item.base_tax_invoiced) ), 0) )  ))"))
                
                
                    ->columns(array('xqty_ordered'=>'IFNULL(item2.qty_ordered, item.qty_ordered)'))
                    ->columns(array('xqty_invoiced'=>'IFNULL(item2.qty_invoiced, item.qty_invoiced)'))
                    ->columns(array('xqty_shipped'=>'IFNULL(item2.qty_shipped, item.qty_shipped)'))
                    ->columns(array('xqty_refunded'=>'IFNULL(item2.qty_refunded, item.qty_refunded)'))                                
        ;

        # Add address data to query
        $this->_addAddress($collection);

        $this->setDateFilter($from, $to)->setState();

        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        if (isset($storeIds))
        {
            $this->setStoreFilter($storeIds);
        }
        $this->addOrderItems()->_addCustomerInfo();
        $this->_addManufacturer($collection);

        if (!$this->getCustomOption('include_refunded')){
            $collection->getSelect()
                       ->where('? > 0', new Zend_Db_Expr('(item.qty_ordered - item.qty_refunded)'))
                       ;
        }


        # Getting data from diff query
        # It slowest, but it work!
        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
            $this->_prepareAddressesFor1710($from, $to, (isset($storeIds) ? $storeIds : null));
        }
        # End of getting data

        $this->_prepareData();

        return $this->getCustomVarData();
    }



    public function _prepareCollection()
    {
        $this
            ->_setUpReportKey()
            ->_setUpFilters()            
            ;
       
        # Start aggregator
        $date_from = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $date_to = $this->_getMysqlToFormat($this->getFilter('report_to'));
        $this->getAggregator()->prepareAggregatedCollection($date_from, $date_to);

        //parent::_prepareCollection();


        $this->setCollection($collection = $this->getAggregator()->getAggregatetCollection());
                
        if ($sort = $this->_getSort()){
            $collection->addOrder($sort, $this->_getDir());
            $this->getColumn($sort)->setDir($this->_getDir());

        } else {
            $collection->getSelect()->order('order_created_at DESC');
        }        

        $this->_saveFilters();


        $this->_setColumnFilters();
    }

    /**
     * Set up store ids to filter collection
     * @param int|array $storeIds
     * @return AW_Advancedreports_Block_Advanced_Grid
     */
    public function setStoreFilter($storeIds)
    {
        $this->getCollection()->getSelect()
                ->where("e.store_id in ('".implode("','", $storeIds)."')");

        return $this;
    }

    /**
     * Set up date filter to collection of grid
     * @param Datetime $from
     * @param Datetime $to
     * @return AW_Advancedreports_Block_Advanced_Grid
     */
    public function setDateFilter($from, $to)
    {
        $filterField = $this->_helper()->confOrderDateFilter();
        $this->getCollection()->getSelect()
                            ->where("e.{$filterField} >= ?", $from)
                            ->where("e.{$filterField} <= ?", $to);
        return $this;
    }

    protected function _addCustomerInfo()
    {
        $customerEntity = $this->getTable('customer_entity');
        $customerGroup = $this->getTable('customer_group');

        $this->getCollection()->getSelect()
                ->joinLeft(array('c_entity'=>$customerEntity), "e.customer_id = c_entity.entity_id", array())
                ->joinLeft(array('c_group'=>$customerGroup), "IFNULL(c_entity.group_id, 0) = c_group.customer_group_id", array('customer_group'=>"c_group.customer_group_code"))

                ;

        return $this;
    }

    protected function _addOptionToCache($id, $value)
    {
        $this->_optCache[$id] = $value;
    }

    protected function _optionInCache($id)
    {
        if (count($this->_optCache)){
            foreach ($this->_optCache as $key=>$value){
                if ($key == $id){
                    return $value;
                }
            }
        }
    }
    
    protected function _getManufacturer( $option_id )
    {
        if (!$this->_optCollection)
        {
            $this->_optCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setStoreFilter(0, false)
                ->load();
        }
        # seach in quick cache
        if ($val = $this->_optionInCache($option_id)){
            return $val;
        }
        # search in chached collection
        foreach ($this->_optCollection as $item)
        {
            if ( $option_id == $item->getOptionId() ){
                $this->_addOptionToCache($option_id, $item->getValue());
                return $item->getValue();
            }
        }
        return null;
    }
    
    public function setState()
    {
        if (Mage::helper('advancedreports')->checkVersion('1.4.0.0')){
            $this->getCollection()->addAttributeToFilter('status', explode( ",", Mage::helper('advancedreports')->confProcessOrders() ));    
        } else {
             $entityValues = $this->getCollection()->getTable('sales_order_varchar');
            $entityAtribute = $this->getCollection()->getTable('eav_attribute');
            $this->getCollection()->getSelect()
                    ->join( array('attr'=>$entityAtribute), "attr.attribute_code = 'status'", array())
                    ->join( array('val'=>$entityValues), "attr.attribute_id = val.attribute_id AND ".$this->_getProcessStates()." AND e.entity_id = val.entity_id", array())
                    ;           
        }        
        return $this;
    }

    protected function _prepareAddressesFor1710($from, $to, $storeIds)
    {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addAttributeToFilter('status', explode( ",", Mage::helper('advancedreports')->confProcessOrders() ));
        $filterField = Mage::helper('advancedreports')->confOrderDateFilter();
        # Filter stores
        if ($storeIds){
            $collection->getSelect()
                    ->where("e.store_id in ('".implode("','", $storeIds)."')");            
        }

        # Filter date
        $collection->getSelect()
                # Filter date
                ->where("e.{$filterField} >= ?", $from)
                ->where("e.{$filterField} <= ?", $to);   
                ;
            

        # Add address
        $entityValue = $this->getTable('sales_order_entity_varchar');
        $entityAtribute = $this->getTable('eav_attribute');
        $entityType = $this->getTable('eav_entity_type');
        $orderEntity = $this->getTable('sales_order_entity');

        $collection->getSelect()
            ->joinLeft(array('_eavType'=>$entityType), "_eavType.entity_type_code = 'order_address'", array())
            ->joinLeft(array('_addrTypeAttr'=>$entityAtribute), "_addrTypeAttr.entity_type_id = _eavType.entity_type_id AND _addrTypeAttr.attribute_code = 'address_type'", array())
            ->joinLeft(array('_addrCountryValueAttr'=>$entityAtribute), "_addrCountryValueAttr.entity_type_id = _eavType.entity_type_id AND _addrCountryValueAttr.attribute_code = 'country_id'", array())
            ->joinLeft(array('_addrCityValueAttr'=>$entityAtribute), "_addrCityValueAttr.entity_type_id = _eavType.entity_type_id AND _addrCityValueAttr.attribute_code = 'city'", array())
            ->joinLeft(array('_addrRegionValueAttr'=>$entityAtribute), "_addrRegionValueAttr.entity_type_id = _eavType.entity_type_id AND _addrRegionValueAttr.attribute_code = 'region'", array())
            ->joinLeft(array('_addrPostcodeValueAttr'=>$entityAtribute), "_addrPostcodeValueAttr.entity_type_id = _eavType.entity_type_id AND _addrPostcodeValueAttr.attribute_code = 'postcode'", array())
            ->joinLeft(array('_addrEmailValueAttr'=>$entityAtribute), "_addrEmailValueAttr.entity_type_id = _eavType.entity_type_id AND _addrEmailValueAttr.attribute_code = 'email'", array())

            # Shipping values
            ->joinRight(array('_orderEntity_ship'=>$orderEntity), "_orderEntity_ship.entity_type_id = _eavType.entity_type_id AND _orderEntity_ship.parent_id = e.entity_id", array())
            ->joinRight(array('_addrTypeVal_ship'=>$entityValue), "_addrTypeVal_ship.attribute_id = _addrTypeAttr.attribute_id AND _addrTypeVal_ship.entity_id = _orderEntity_ship.entity_id AND _addrTypeVal_ship.value = 'shipping'", array())
            ->joinRight(array('_addrCountryVal_ship'=>$entityValue), "_addrCountryVal_ship.attribute_id = _addrCountryValueAttr.attribute_id AND _addrCountryVal_ship.entity_id = _orderEntity_ship.entity_id", array())
            ->joinRight(array('_addrCityVal_ship'=>$entityValue), "_addrCityVal_ship.attribute_id = _addrCityValueAttr.attribute_id AND _addrCityVal_ship.entity_id = _orderEntity_ship.entity_id", array())
            ->joinRight(array('_addrRegionVal_ship'=>$entityValue), "_addrRegionVal_ship.attribute_id = _addrRegionValueAttr.attribute_id AND _addrRegionVal_ship.entity_id = _orderEntity_ship.entity_id", array())
            ->joinRight(array('_addrPostcodeVal_ship'=>$entityValue), "_addrPostcodeVal_ship.attribute_id = _addrPostcodeValueAttr.attribute_id AND _addrPostcodeVal_ship.entity_id = _orderEntity_ship.entity_id", array())
            ->joinRight(array('_addrEmailVal_ship'=>$entityValue), "_addrEmailVal_ship.attribute_id = _addrEmailValueAttr.attribute_id AND _addrEmailVal_ship.entity_id = _orderEntity_ship.entity_id", array())

            # Billing values
            ->joinRight(array('_orderEntity_bill'=>$orderEntity), "_orderEntity_bill.entity_type_id = _eavType.entity_type_id AND _orderEntity_bill.parent_id = e.entity_id", array())
            ->joinRight(array('_addrTypeVal_bill'=>$entityValue), "_addrTypeVal_bill.attribute_id = _addrTypeAttr.attribute_id AND _addrTypeVal_bill.entity_id = _orderEntity_bill.entity_id AND _addrTypeVal_bill.value = 'billing'", array())
            ->joinRight(array('_addrCountryVal_bill'=>$entityValue), "_addrCountryVal_bill.attribute_id = _addrCountryValueAttr.attribute_id AND _addrCountryVal_bill.entity_id = _orderEntity_bill.entity_id", array())
            ->joinRight(array('_addrCityVal_bill'=>$entityValue), "_addrCityVal_bill.attribute_id = _addrCityValueAttr.attribute_id AND _addrCityVal_bill.entity_id = _orderEntity_bill.entity_id", array())
            ->joinRight(array('_addrRegionVal_bill'=>$entityValue), "_addrRegionVal_bill.attribute_id = _addrRegionValueAttr.attribute_id AND _addrRegionVal_bill.entity_id = _orderEntity_bill.entity_id", array())
            ->joinRight(array('_addrPostcodeVal_bill'=>$entityValue), "_addrPostcodeVal_bill.attribute_id = _addrPostcodeValueAttr.attribute_id AND _addrPostcodeVal_bill.entity_id = _orderEntity_bill.entity_id", array())
            ->joinRight(array('_addrEmailVal_bill'=>$entityValue), "_addrEmailVal_bill.attribute_id = _addrEmailValueAttr.attribute_id AND _addrEmailVal_bill.entity_id = _orderEntity_bill.entity_id", array())


            ->columns(array('country_id' => 'IFNULL(_addrCountryVal_ship.value, _addrCountryVal_bill.value)'))
            ->columns(array('city' => 'IFNULL(_addrCityVal_ship.value, _addrCityVal_bill.value)'))
            ->columns(array('region' => 'IFNULL(_addrRegionVal_ship.value, _addrRegionVal_bill.value)'))
            ->columns(array('postcode' => 'IFNULL(_addrPostcodeVal_ship.value, _addrPostcodeVal_bill.value)'))
            ->columns(array('customer_email' => 'IFNULL(_addrEmailVal_ship.value, _addrEmailVal_bill.value)'))
            ;

        if ($collection->getSize()){
            foreach ($collection as $order){
                $this->_addresses[$order->getId()] = array(
                    'country_id'=>$order->getCountryId(),
                    'city'=>$order->getCity(),
                    'region'=>$order->getRegion(),
                    'postcode'=>$order->getPostcode(),
                );
            }
        }
        return $this;
    }

    protected function _prepareData()
    {
//        echo $this->getCollection()->getSelect()->__toString();
        foreach ($this->getCollection() as $item)        
        {
            $row = $item->getData();



            # Getting data from diff query
            # It slowest, but work!
            if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
                $row['order_ship_country'] = $this->_addresses[$row['order_id']]['country_id'];
                $row['order_ship_city'] = $this->_addresses[$row['order_id']]['city'];
                $row['order_ship_region'] = $this->_addresses[$row['order_id']]['region'];
                $row['order_ship_postcode'] = $this->_addresses[$row['order_id']]['postcode'];
                $row['order_ship_email'] = $this->_addresses[$row['order_id']]['email'];
            }
            # End of getting data


            if (isset( $row['order_ship_country_id'] )){
                $row['order_ship_country'] = $row['order_ship_country_id'];    
            }        
            if (isset( $row['order_bil_country_id'] )){
                $row['order_bil_country'] = $row['order_bil_country_id'];    
            }            
            
            # Billing/Shipping logic
            if (isset($row['order_ship_country'])){
                $row['order_country'] = $row['order_ship_country'];
            } elseif(isset($row['order_bil_country'])) {
                $row['order_country'] = $row['order_bil_country'];
            }
            if (isset($row['order_ship_region'])){
                $row['order_region'] = $row['order_ship_region'];
            } elseif(isset($row['order_bil_region'])) {
                $row['order_region'] = $row['order_bil_region'];
            }
            if (isset($row['order_ship_city'])){
                $row['order_city'] = $row['order_ship_city'];
            } elseif(isset($row['order_bil_city'])) {
                $row['order_city'] = $row['order_bil_city'];
            }            
            if (isset($row['order_ship_postcode'])){
                $row['order_postcode'] = $row['order_ship_postcode'];
            } elseif(isset($row['order_bil_postcode'])) {
                $row['order_postcode'] = $row['order_bil_postcode'];
            }
            if (isset($row['order_ship_email'])){
                $row['customer_email'] = $row['order_ship_email'];
            } elseif(isset($row['order_bil_email'])) {
                $row['customer_email'] = $row['order_bil_email'];
            }
                        
            
            if (isset($row['simple_sku'])){
                $row['sku'] = $row['simple_sku'];
            } 

               if (isset($row['sku'])){
                $this->_addCustomData($row);
            }                        
        }
        return $this;
    }

    protected function _prepareColumns()
    {    

        $def_value = sprintf("%f", 0);
        $def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);    
    
        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('advancedreports')->__('Order #'),
            'index' => 'order_increment_id',
            'type' => 'text',
            'width' => '80px',
            'database_type' => 'VARCHAR(255) NULL',
        ));        
        
        $this->addColumn('order_created_at', array(
            'header' => Mage::helper('advancedreports')->__('Order Date'),
            'index' => 'order_created_at',
            'type' => 'datetime',
            'width' => '140px',
            'is_period_key' => true,
            'database_type' => 'DATETIME NOT NULL',
        ));

        $this->addColumn('xsku', array(
            'header'    =>Mage::helper('advancedreports')->__('SKU'),
            'width'     =>'120px',
            'index'     =>'xsku',
            'type'      =>'text',
            'database_type' => 'VARCHAR(255) NOT NULL',
        ));
        

        $this->addColumn('customer_email', array(
            'header' => Mage::helper('advancedreports')->__('Customer Email'),
            'index' => 'customer_email',
            'type' => 'text',
            'width' => '100px',
            'database_type' => 'VARCHAR(255) NULL',
        ));

        $this->addColumn('customer_group', array(
            'header' => Mage::helper('advancedreports')->__('Customer Group'),
            'index' => 'customer_group',
            'type' => 'text',
            'width' => '100px',
            'database_type' => 'VARCHAR(255) NULL',
        ));

        $this->addColumn('order_ship_country', array(
            'header' => Mage::helper('advancedreports')->__('Country'),
            'index' => 'order_ship_country',
            'type' => 'country',
            'width' => '100px',
            'database_type' => 'VARCHAR(10) NULL',
        ));           
        
        $this->addColumn('order_ship_region', array(
            'header' => Mage::helper('advancedreports')->__('Region'),
            'index' => 'order_ship_region',
            'type' => 'text',
            'width' => '100px',
            'database_type' => 'VARCHAR(255) NULL',
        ));
        
        $this->addColumn('order_ship_city', array(
            'header' => Mage::helper('advancedreports')->__('City'),
            'index' => 'order_ship_city',
            'type' => 'text',
            'width' => '100px',
            'database_type' => 'VARCHAR(255) NULL',
        ));                  
                
        $this->addColumn('order_ship_postcode', array(
            'header' => Mage::helper('advancedreports')->__('Zip Code'),
            'index' => 'order_ship_postcode',
            'type' => 'text',
            'width' => '60px',
            'database_type' => 'VARCHAR(255) NULL',
        ));            

        $this->addColumn('name', array(
            'header'    =>Mage::helper('advancedreports')->__('Product Name'),
            'index'     =>'name',
            'type'      =>'text',
            'database_type' => 'VARCHAR(255) NULL',
        ));

        $this->addColumn('product_manufacturer', array(
            'header'    =>Mage::helper('advancedreports')->__('Manufacturer'),
            'index'     =>'product_manufacturer',
            'type'      =>'text',
            'width'     =>'100px',
            'database_type' => 'VARCHAR(255) NULL',
        ));

        $this->addColumn('xqty_ordered', array(
            'header'    =>Mage::helper('advancedreports')->__('Qty. Ordered'),
            'width'     =>'60px',
            'index'     =>'xqty_ordered',
            'total'     =>'sum',
            'type'      =>'number',
            'database_type' => 'decimal(12,4) NULL',

        ));

        $this->addColumn('xqty_invoiced', array(
            'header'    =>Mage::helper('advancedreports')->__('Qty. Invoiced'),
            'width'     =>'60px',
            'index'     =>'xqty_invoiced',
            'total'     =>'sum',
            'type'      =>'number',
            'database_type' => 'decimal(12,4) NULL',

        ));

        $this->addColumn('xqty_shipped', array(
            'header'    =>Mage::helper('advancedreports')->__('Qty. Shipped'),
            'width'     =>'60px',
            'index'     =>'xqty_shipped',
            'total'     =>'sum',
            'type'      =>'number',
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('xqty_refunded', array(
            'header'    =>Mage::helper('advancedreports')->__('Qty. Refunded'),
            'width'     =>'60px',
            'index'     =>'xqty_refunded',
            'total'     =>'sum',
            'type'      =>'number',
            'database_type' => 'decimal(12,4) NULL',
        ));


        $this->addColumn('base_xprice', array(
            'header'    =>Mage::helper('advancedreports')->__('Price'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_xprice',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'disable_total' => 1,
            'database_type' => 'decimal(12,4) NULL',

        ));                    
        
        $this->addColumn('base_row_subtotal', array(
            'header'    =>Mage::helper('advancedreports')->__('Subtotal'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_subtotal',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));        

        $this->addColumn('base_tax_amount', array(
            'header'    =>Mage::helper('advancedreports')->__('Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_amount',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));
        
        $this->addColumn('base_discount_amount', array(
            'header'    =>Mage::helper('advancedreports')->__('Discounts'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_discount_amount',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));                

        $this->addColumn('base_tax_amount', array(
            'header'    =>Mage::helper('advancedreports')->__('Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_amount',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));


        $this->addColumn('base_row_xtotal', array(
            'header'    =>Mage::helper('advancedreports')->__('Total'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xtotal',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_row_xtotal_incl_tax', array(
            'header'    =>Mage::helper('advancedreports')->__('Total Incl. Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xtotal_incl_tax',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_row_xinvoiced', array(
            'header'    =>Mage::helper('advancedreports')->__('Invoiced'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xinvoiced',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_tax_invoiced', array(
            'header'    =>Mage::helper('advancedreports')->__('Tax Invoiced'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_invoiced',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_row_xinvoiced_incl_tax', array(
            'header'    =>Mage::helper('advancedreports')->__('Invoiced Incl. Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xinvoiced_incl_tax',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_row_xrefunded', array(
            'header'    =>Mage::helper('advancedreports')->__('Refunded'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xrefunded',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_tax_xrefunded', array(
            'header'    =>Mage::helper('advancedreports')->__('Tax Refunded'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_xrefunded',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));

        $this->addColumn('base_row_xrefunded_incl_tax', array(
            'header'    =>Mage::helper('advancedreports')->__('Refunded Incl. Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xrefunded_incl_tax',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
            'database_type' => 'decimal(12,4) NULL',
        ));


        $this->addColumn('view_order',
            array(
                'header'    => Mage::helper('advancedreports')->__('View Order'),
                'width'     => '70px',
                'type'      => 'action',
                'align'     =>'left',
                'getter'    => 'getOrderId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('advancedreports')->__('View'),
                        'url'     => array(
                            'base'=>'adminhtml/sales_order/view',
                            'params'=>array()
                        ),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'order_id',
                'database_type' => 'INT UNSIGNED NULL',
        ));
        
        $this->addColumn('view_product',
            array(
                'header'    => Mage::helper('advancedreports')->__('View Product'),
                'width'     => '70px',
                'type'      => 'action',
                'align'     =>'left',
                'getter'    => 'getProductId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('advancedreports')->__('View'),
                        'url'     => array(
                            'base'=>'adminhtml/catalog_product/edit',
                            'params'=>array()
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'product_id',
                'database_type' => 'INT UNSIGNED NULL',
        ));

        $this->addExportType('*/*/exportOrderedCsv', Mage::helper('advancedreports')->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', Mage::helper('advancedreports')->__('Excel'));
        return $this;
    }

    public function getChartType()
    {
        return 'none';
    }

    public function hasRecords()
    {
        return false;
    }

    public function getPeriods()
    {
        return array();
    }        
    
    public function getGridUrl()
    {
        $params = Mage::app()->getRequest()->getParams();
        $params['_secure'] = Mage::app()->getStore(true)->isCurrentlySecure();        
        return $this->getUrl('*/*/grid', $params);
    }

    public function  hasAggregation()
    {
        return true;
    }

}
