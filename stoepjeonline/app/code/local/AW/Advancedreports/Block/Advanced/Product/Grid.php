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
class AW_Advancedreports_Block_Advanced_Product_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_PRODUCTS;
    protected $_skus = array();
    protected $_filterSkus = array();
    protected $_skuColumns = array();

    protected $_columnConfigEnabled = false;

    /**
     * Additional skus (Optional skus) for main product
     * @var array
     */
    protected $_additionalSkus = array();

    /**
     * If sku inputed with mask, we restore it here.
     * For future group by sky request
     * @var array
     */
    protected $_maskedSkus = array();

    /**
     * Column index increment
     * @var integer
     */
    protected $_columnIncrement = 0;

    /**
     * Detail grouped
     */
    const DETAIL_SUMM = 0;

    /**
     * Detail detailed
     */
    const DETAIL_DETAIL = 1;

    protected $_detailOptions = array(
        self::DETAIL_SUMM => 'Grouped',
        self::DETAIL_DETAIL => 'Detailed',
    );

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( Mage::helper('advancedreports')->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridProduct');
        $this->setShowAdditionalSelector(true);
    }

    public function getDetailKey()
    {
        return $this->getFilter('detail_key');
    }

    public function getGrouped()
    {
        return ($this->getDetailKey() == self::DETAIL_SUMM);
    }

    public function getAdditionalSelectorHtml()
    {
        $out = '<div class="f-left" style="margin-right: 3px;">';
        $out .= '<select style="width: 7em;" id="detail_key" name="detail_key" class="left-col-block">';
        foreach ($this->_detailOptions as $value=>$label){
            $out .= "<option ".(($this->getDetailKey() == $value)?'selected ':'')."value=\"$value\">".Mage::helper('advancedreports')->__($label)."</option>";
        }
        $out .= '</select>';
        $out .= '</div>';
        return $out;
    }



    /**
     * Retrives initialization array for custom report option
     * @return array
     */
    public function  getCustomOptionsRequired()
    {
        $array = parent::getCustomOptionsRequired();

        $addArray = array(
            array(
                'id'=>'product_sku_limit',
                'type'=> 'text',
                'args' => array(
                    'label'     => Mage::helper('advancedreports')->__('The number of records in the SKU Advisor'),
                    'title'     => Mage::helper('advancedreports')->__('The number of records in the SKU Advisor'),
                    'name'      => 'product_sku_limit',
                    'class'     => '',
                    'required'  => true,
                ),
                'default' => '10'
            ),

        );
        return array_merge($array, $addArray);
    }

    protected function _prepareGrid()
    {
        $this->_prepareMassactionBlock();
        $this->_prepareCollection();
        $this->_prepareColumns();
        parent::_prepareData();
        return $this;
    }

    protected function _prepareLayout()
    {
        # prepare SKUs
        if ( $filter = $this->getParam($this->getVarNameFilter(), null) ){
            $data = array();
            $filter = base64_decode($filter);
            
            $filter = str_replace("%26", "XXXDUMMYAMPERSANDXXX", $filter);
            
            parse_str(urldecode($filter), $data);
            
            foreach ($data as $key => &$value){
                $value = str_replace("XXXDUMMYAMPERSANDXXX", "&", $value);
            }
            
            if ( isset( $data['product_sku'] ) ){
                $this->_filters['detail_key'] = $data['detail_key'];                
                $this->setSkus( $data['product_sku'] );                
            }
            $this->_helper()->setSkus( $data['product_sku'] );
        } else {
            if ( $skus = $this->_helper()->getSkus() ){
                $this->setSkus( $skus );
            }
        }
        parent::_prepareLayout();
        return $this;
    }

    public function getDisableAutoload()
    {
        return true;
    }

    public function getHideShowBy()
    {
        return false;
    }

    public function getIsSalesByProduct()
    {
        return true;
    }

    protected function _addCustomData($row)
    {
        $key = $this->getFilter('reload_key');
        if ( count( $this->_customData ) )
        {
            foreach ( $this->_customData as &$d )
            {
                if ( $d['period'] == $row['period'] )
                {
                    if ( isset( $d[$row['sku']] ) )
                    {
                        $qty = $d[ $row['sku'] ];
                        unset($d[ $row['sku'] ]);
                        if ( isset( $d[ $row['column_id'] ] ) )
                        {
                            unset($d[ $row['column_id'] ]);
                        }
            
                        if ($key === 'total')
                        {
                            $d[ $row['sku'] ] = $row['total'] + $qty;
                            $d[ $row['column_id'] ] = $row['total'] + $qty;
                        }
                        else
                        {
                            $d[ $row['sku'] ] = $row['ordered_qty'] + $qty;
                            $d[ $row['column_id'] ] = $row['ordered_qty'] + $qty;
                        }
                        
                    }
                    else
                    {
                        if ($key === 'total')
                        {
                            $d[ $row['sku'] ] = $row['total'];
                            $d[ $row['column_id'] ] = $row['total'];
                        }
                        else
                        {
                            $d[ $row['sku'] ] = $row['ordered_qty'];
                            $d[ $row['column_id'] ] = $row['ordered_qty'];
                        }                        
                    }
                    return $this;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    protected function _isValidSku($sku)
    {       
        return $this->_getProductName($sku);
    }

    protected function _getProductName($sku, $addSku = null, $isAdditional = false)
    {                        
        if ($isAdditional){
            $out = Mage::helper('advancedreports')->getProductNameBySku($sku);
            $append = str_replace($sku, "", $addSku);
            $out .= " ($append)";
            return $out;
        } else {                                    
            return $this->_helper()->getProductNameBySku($sku);
        }
    }

    protected function _registerProduct($sku){

        $sku = strtolower(trim($sku));
        $this->_skus[] = $sku;
        $this->_filterSkus[] = Mage::helper('advancedreports')->getProductSkuBySku($sku);
        $this->_skuColumns[$sku] = 'column'.$this->_columnIncrement;
        $this->_columnIncrement++;

        # Find skus with Options Appendixes
        if (true) {

            Varien_Profiler::start('aw::advancedreports::product::find_additional_skus');
            $searchSku = $sku;

            $items = Mage::getModel('sales/order_item')->getCollection();
            $items->addFieldToFilter('sku', array('like'=>$sku.'%'));
            $items->getSelect()->where('sku <> ?', $sku)->group('sku');

            foreach ($items as $item){
                $this->_additionalSkus[$sku][] = $item->getSku();
                $this->_filterSkus[] = $item->getSku();
                if (!$this->getGrouped()){
                    $this->_skuColumns[$item->getSku()] = 'column'.$this->_columnIncrement;
                    $this->_columnIncrement++;
                }
            }
           
            Varien_Profiler::stop('aw::advancedreports::product::find_additional_skus');
        }
    }

    protected function _registerVirtualSku($request, $sku)
    {
        $this->_maskedSkus[$request][] = $sku;
        
        $request = strtolower(trim($request));
        $sku = strtolower(trim($sku));
        
        if (array_search($request, $this->_skus) === false){
            $this->_skus[] = $request;
            $this->_skuColumns[$request] = 'column'.$this->_columnIncrement;
            $this->_columnIncrement++;
        }
        $this->_filterSkus[] = $sku;


        # Find skus with Options Appendixes
        if (true) {
            Varien_Profiler::start('aw::advancedreports::product::find_additional_skus');
            $searchSku = $sku;

            $items = Mage::getModel('sales/order_item')->getCollection();
            $items->addFieldToFilter('sku', array('like'=>$sku.'%'));
            $items->getSelect()->where('sku <> ?', $sku)->group('sku');

            foreach ($items as $item){
                $this->_additionalSkus[$sku][] = $item->getSku();
                $this->_filterSkus[] = $item->getSku();
            }
            Varien_Profiler::stop('aw::advancedreports::product::find_additional_skus');
        }
    }
    
    /**
     * Parse filter string and set up skus to report them 
     * @param string $value
     */
    public function setSkus($value)
    {               
        $skus = explode(',', $value);
        if ($skus && is_array($skus) && count($skus)){                                    
            foreach ($skus as $sku){
                #Masked sku     
                if (strpos(trim($sku), "*") !== false){
                    
                    # Remove double stars
                    while (strpos($sku, "**") !== false){
                        $sku = str_replace("**", "*", trim($sku));
                    }
                    $request = trim($sku);
                    $sku = str_replace("*", "%", trim($sku));

                    # Search mask for Product's sku
                    $products = Mage::getModel('catalog/product')->getCollection();
                    $products->addFieldToFilter('sku', array('like'=>$sku));

                    foreach ($products as $product){                       
                        $sku = $product->getSku();
                        if ( trim($sku) && $this->_isValidSku(trim($sku)) )
                        {
                            if ($this->getGrouped()){
                                $this->_registerVirtualSku($request, $sku);
                            } else {
                                $this->_registerProduct($sku);
                            }
                        }
                    }
                    
                    # Search mask for orders' sku
                    $items = Mage::getModel('sales/order_item')->getCollection();
                    $items->addFieldToFilter('sku', array('like'=>$sku));
                    if (count($products->getAllIds())){
                        $items->addFieldToFilter('product_id', array('nin'=>$products->getAllIds()));
                    }
                    $items->getSelect()->group('sku');

                    foreach ($items as $item){
                        $sku = $item->getSku();
                        if ( trim($sku) && $this->_isValidSku(trim($sku)) )
                        {
                            if ($this->getGrouped()){
                                $this->_registerVirtualSku($request, $sku);
                            } else {
                                $this->_registerProduct($sku);
                            }
                        }
                    }

                # General sku
                } elseif ( trim($sku) && $this->_isValidSku(trim($sku)) ){
                    $this->_registerProduct($sku);
                }
            }
        }      
    }

    public function getColumnBySku($sku)
    {
        if ( $sku && isset( $this->_skuColumns[$sku] ) )
        {
            return $this->_skuColumns[$sku];
        }    
    }

    public function getSkus()
    {
        return $this->_skus;
    }

    public function _prepareCollection()
    {        
        parent::_prepareOlderCollection();
        $this->getCollection()
            ->initReport('reports/product_ordered_collection');
        $this->_prepareData();
        return $this;
    }

    /*
     *  This part build collection for every period filter by entered skus.
     *  It's more optimal variant for oprimisation
     */
    protected function _setSkusFilter($collection)
    {
        if ($filter = $this->_getWhereSkusFilter())
        {
            $collection->getSelect()->where("({$filter})");
        }
        return $this;
    }

    protected function _setStateFilter($collection)
    {
//        $entityValues = $collection->getTable('sales_order_varchar');
//        $entityAtribute = $collection->getTable('eav_attribute');
//        $collection->getSelect()
//                ->join( array('attr'=>$entityAtribute), "attr.attribute_code = 'status'", array())
//                ->join( array('val'=>$entityValues), "attr.attribute_id = val.attribute_id AND ".$this->_getProcessStates()." AND e.entity_id = val.entity_id", array())
//                ;
        //TODO Clear # after tests
        $collection->addAttributeToFilter('status', explode( ",", Mage::helper('advancedreports')->confProcessOrders() ));

        return $this;
    }

    protected function _setPeriodFilter($collection, $from, $to)
    {
        $filterField = Mage::helper('advancedreports')->confOrderDateFilter();
        $collection->getSelect()
                            ->where("e.{$filterField} >= ?", $from)
                            ->where("e.{$filterField} <= ?", $to);
        return $this;
    }
    
    protected function _setStoreFilter($collection, $storeIds)
    {
        $collection->getSelect()
                ->where("e.store_id in ('".implode("','", $storeIds)."')");
        return $this;            
    }

    protected function _getWhereSkusFilter()
    {
        if ( count($this->_filterSkus) )
        {
            $filter = '';
            $is_first = true;
            foreach($this->_filterSkus as $sku)
            {
            if (!$is_first)
            {
                $filter .= ' OR ';
            }
            $filter .= "item.sku = '{$sku}'";
            $is_first = false;
            }
            return $filter;
        }
        return null;
    }
    
    protected function _addItems($collection)
    {
        $itemTable = $this->getTable('sales_flat_order_item');
        $collection->getSelect()
                ->join( array('item'=>$itemTable), "e.entity_id = item.order_id AND item.parent_item_id IS NULL", array( 'sum_qty' => 'SUM(item.qty_ordered)',  'sum_total' => 'SUM(item.base_row_total - item.base_discount_amount + item.base_tax_amount)', 'name' => 'name', 'sku'=>'sku' ) )
                ->group('item.sku');
        return $this;
    }

    protected function _getOrderCollection($from, $to)
    {
        $collection = Mage::getModel('advancedreports/order')->getCollection();

        if ($this->_checkVer('1.7.1.0') && !$this->_checkVer('1.8.0.0')){
            $orderTable = $this->getTable('sales_order');
        } elseif (Mage::helper('advancedreports')->checkVersion('1.4.1.0')){
            $orderTable = $this->getTable('sales_flat_order');
        } else {
            $orderTable = $this->getTable('sales_order');
        }
        
        $collection->getSelect()->reset();
        $collection->getSelect()->from(array('e'=>$orderTable), array());
    
        $this->_setPeriodFilter($collection, $from, $to)
            ->_setSkusFilter($collection)
            ->_setStateFilter($collection)
            ->_addItems($collection);
    
        # check Store Filter
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        if (isset($storeIds))
        {
            $this->_setStoreFilter($collection, $storeIds);
        }
        return $collection;
    }

    /**
     * Search sku in additionl skus.
     * Retrives existanse flag.
     * 
     * @param string $sku Sku for search
     * @return boolean
     */
    protected function _isInAdditional($sku)
    {
        foreach ($this->_additionalSkus as $k=>$skus){
            if (isset($skus) && is_array($skus)){
                foreach ($skus as $sSku){
                    if ($sSku == $sku){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Search sku in virtual skus.
     * Retrives existanse flag.
     *
     * @param string $sku Sku for search
     * @return boolean
     */
    protected function _isInVirtualSku($sku)
    {
        foreach ($this->_maskedSkus as $k=>$skus){
            if (isset($skus) && is_array($skus)){
                foreach ($skus as $sSku){
                    if ($sSku == $sku){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function _prepareData()
    {
        $chartLabels = array();
        if ( count($this->getSkus()) )
        {
            # primary analise
            foreach ( $this->getCollection()->getIntervals() as $_index=>$_item )
            {
                $items = $this->_getOrderCollection($_item['start'], $_item['end']);
//                echo $items->getSelect()->__toString().'<hr>';
                $row['period'] = $_item['title'];
                $this->_addCustomData($row);
                foreach ( $items as $item )
                {
                    if ( in_array( strtolower($item->getSku()), $this->_skus ) || $this->_isInAdditional($item->getSku()) || $this->_isInVirtualSku($item->getSku()) )
                    {
                        $row['period'] = $_item['title'];
                        $row['sku'] = strtolower($item->getSku());
                        $row['column_id'] = $this->getColumnBySku( strtolower($item->getSku()) );
                        $row['ordered_qty'] = $item->getSumQty();
                        $row['total'] = $item->getSumTotal();
                        $this->_addCustomData($row);
                    }
                }
            }

            # final preporation of data
            if ( count( $this->_customData ) ){
                foreach ( $this->getSkus() as $sku ){

                    foreach ( $this->_customData as &$d ){
                        if ( !isset( $d[$sku] ) ){
                            $d[$sku] = 0;
                        }
                    }

                    if ($this->getGrouped()){

                        # If result is grouped
                        if (isset($this->_additionalSkus[$sku]) && count($this->_additionalSkus[$sku])){
                            foreach ($this->_additionalSkus[$sku] as $addSku){

                                foreach ( $this->_customData as &$d ){
                                    if ( isset( $d[$addSku] ) ){
                                        $d[$sku] += $d[$addSku];
                                        $d[$this->getColumnBySku($sku)] = $d[$sku];
                                    }
                                }
                            }
                        }

                        if ($this->_isVirtualSku($sku)){
                            if (isset($this->_maskedSkus[$sku])){
                                foreach ($this->_maskedSkus[$sku] as $chSku){
                                    # Check additional masked skus

                                    if (isset($this->_additionalSkus[$chSku]) && count($this->_additionalSkus[$chSku])){
                                        foreach ($this->_additionalSkus[$chSku] as $addSku){

                                            foreach ( $this->_customData as &$d ){
                                                if ( isset($d[$addSku]) && isset($d[$chSku]) ){
                                                    $d[$chSku] += $d[$addSku];
                                                    $d[$this->getColumnBySku($chSku)] = $d[$chSku];
                                                }
                                            }
                                        }
                                    }

                                    # Check basical masked sku
                                    foreach ( $this->_customData as &$d ){
                                        if ( isset( $d[$chSku] ) ){
                                            $d[$sku] = (string)($d[$sku] + $d[$chSku]);
                                            $d[$this->getColumnBySku($sku)] = (string)$d[$sku];
                                        }
                                    }


                                }
                            }
                        }

                    } else {
                        # If result is detailed
                        if (isset($this->_additionalSkus[$sku]) && count($this->_additionalSkus[$sku])){
                            foreach ($this->_additionalSkus[$sku] as $addSku){

                                foreach ( $this->_customData as &$d ){
                                    if ( !isset( $d[$addSku] ) ){
                                        $d[$addSku] = 0;
                                    }
                                }
                            }
                        }
                    }    
                }
            }

            foreach ($this->_skus as $sku)
            {
                if ($this->_isVirtualSku($sku)){
                    $chartLabels[$sku] = $sku;
                } else {
                    $chartLabels[$sku] = Mage::helper('advancedreports')->getProductNameBySku($sku);
                }
                if ($this->getGrouped()){

                } else {
                    if (isset($this->_additionalSkus[$sku]) && count($this->_additionalSkus[$sku])){
                        foreach ($this->_additionalSkus[$sku] as $addSku){
                            $chartLabels[$addSku] = $this->_getProductName($sku, $addSku, true);
                        }
                    }
                }
            }
        }

        $chartKeys = array();
        foreach ($this->_skus as $sku){
            if (array_search($sku, $chartKeys) === false){
                $chartKeys[] = $sku;
            }
            if ($this->getGrouped()){
                # Do somthing
            } else {
                if (isset($this->_additionalSkus[$sku]) && count($this->_additionalSkus[$sku])){
                    foreach ($this->_additionalSkus[$sku] as $addSku){
                        if (array_search($addSku, $chartKeys) === false){
                            $chartKeys[] = $addSku;
                        }
                    }
                }
            }


        }

        # Reclean data
        $newData = array();

        foreach ($this->_customData as $data){
            $newSubData = array();
            foreach ($data as $k=>$v){
                if ($k){
                    $newSubData[$k] = $v;
                }
            }
            $newData[] = $newSubData;
        }

        $this->_customData = $newData;

        Mage::helper('advancedreports')->setChartData( $this->_customData, Mage::helper('advancedreports')->getDataKey( $this->_routeOption ) );
        Mage::helper('advancedreports')->setChartKeys( $chartKeys, Mage::helper('advancedreports')->getDataKey( $this->_routeOption )  );
        Mage::helper('advancedreports')->setChartLabels( $chartLabels, Mage::helper('advancedreports')->getDataKey( $this->_routeOption )  );
        parent::_prepareData();
        return $this;
    }

    /**
     * Retrives TRUE if $sku is virtual
     * @return boolean
     */
    protected function _isVirtualSku($sku)
    {
        foreach ($this->_maskedSkus as $k=>$v){
            if ($k == $sku){
                return true;
            }
        }
        return false;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('periods', array(
            'header'    =>$this->getPeriodText(),
            'width'     =>'120px',
            'index'     =>'period',
            'type'      =>'text',
            'sortable'    => false,
        ));
       
        $key = $this->getFilter('reload_key');
        $def_value = sprintf("%f", 0);
        $def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);
        $def_value = $key === 'total' ? $def_value : '0';
        $type = $key === 'total' ? 'currency' : 'number';
        foreach ( $this->_skus as $sku )
        {
            if ($this->_isVirtualSku($sku) && $this->getGrouped()){
                $this->addColumn( $this->getColumnBySku($sku) , array(
                    'header'    =>$sku,
                    'index'     =>$this->getColumnBySku($sku),
                    'type'      =>$type,
                    'currency_code' => $this->getCurrentCurrencyCode(),
                    'default'  => $def_value,
                ));
            } else {
                $this->addColumn( $this->getColumnBySku($sku) , array(
                    'header'    =>$this->_getProductName($sku),
                    'index'     =>$this->getColumnBySku($sku),
                    'type'      =>$type,
                    'currency_code' => $this->getCurrentCurrencyCode(),
                    'default'  => $def_value,
                ));
            }

            # Add columns with additional filter
            if (!$this->getGrouped()){
                if (isset($this->_additionalSkus[$sku]) && count($this->_additionalSkus[$sku])){
                    foreach ($this->_additionalSkus[$sku] as $addSku){
                        $this->addColumn( $this->getColumnBySku($addSku) , array(
                            'header'    =>$this->_getProductName($sku, $addSku, true),
                            'index'     =>$this->getColumnBySku($addSku),
                            'type'      =>$type,
                            'currency_code' => $this->getCurrentCurrencyCode(),
                            'default'  => $def_value,
                        ));
                    }
                }
            }
        }
        $this->addExportType('*/*/exportOrderedCsv', Mage::helper('advancedreports')->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', Mage::helper('advancedreports')->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_MULTY_LINE;
    }

    public function getPeriods()
    {
        return parent::_getOlderPeriods();
    }

}
