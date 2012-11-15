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
 * Tool: Reports Data aggregator
 */
class AW_Advancedreports_Helper_Tools_Aggregator extends AW_Advancedreports_Helper_Data
{
    /**
     * Type Periods
     */
    const TYPE_PERIODS = 'periods';

    /**
     * Type List
     */
    const TYPE_LIST = 'list';

    /**
     * Table Prefix
     */
    const TABLE_PREFIX = 'aw_arep_aggregated';


    /**
     * Date INdex Field
     */
    const DATE_KEY_FIELD = 'period_key';

    /**
     * Aggregator period
     * @var string
     */
    protected $_type = self::TYPE_PERIODS;

    /**
     * Report id
     * @var string
     */
    protected $_reportId = 'default';

    protected $_init = false;

    protected $_timeType = 'created_at';

    protected $_storeAppendix = 'main';

    protected $_md5 = null;

    /**
     * Grid
     * @var AW_Advancedreports_Block_Advanced_Grid
     */
    protected $_grid = null;

    protected $_collection = array();


    /**
     * Initialize aggregator instance
     * This step is required
     * @param AW_Advancedreports_Block_Advanced_Grid $grid Active grid
     * @param string $type 'list' or 'periods'
     * @param string $aggId Id of report
     * @param string $timeType 'created at' or 'updated_at'
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function initAggregator($grid = null, $type = null, $aggId = null, $timeType = null)
    {
        if ($aggId){
            $this->_reportId = $aggId;
        }

        if($type){
            $this->_type = $type;
        }

        if ($timeType){
            $this->_timeType = $timeType;
        }

        

        if ($grid){
            $this->_grid = $grid;
            $this->_values = $grid->getOptionsValues();
        }

        $this->_init = true;
        return $this;
    }

    /**
     * Retrives resource
     * 
     * @return Mage_Core_Model_Resource
     */
    protected function _getResource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * Creates a connection to resource whenever needed
     *
     * @param string $name
     * @return mixed
     */
    protected function _getConnection($connectionName)
    {
        return $this->_getResource()->getConnection($connectionName);
    }

    /**
     * Retrieve connection for read data
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getReadAdapter()
    {
        return $this->_getConnection('core_read');
    }

    /**
     * Retrieve connection for write data
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWriteAdapter()
    {
        return $this->_getConnection('core_write');
    }

    /**
     * Set tore filter to aggregator
     * @param string|integer|array $store
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function setStoreFilter($store)
    {
        $this->_storeAppendix = is_array($store) ? implode("_", $store) : $store;
        return $this;
    }

    protected function _validateInstance()
    {
        if (!$this->_init){
            Mage::throwException('Aggregator initialization required');
        }
    }

    protected function _getDBTable($tableName)
    {
        return $this->_getResource()->getTableName($tableName);
    }

    /**
     * Retrives table name
     * @return string
     */
    public function getTableName()
    {
        $this->_validateInstance();

        $arr = array(
            self::TABLE_PREFIX,
            md5(implode("_", array(
                $this->_reportId,
                $this->_type,
                $this->_timeType,
                $this->_storeAppendix,
                $this->_values,
            )))
        );

        return substr($this->_getDBTable( implode("_", $arr) ), 0, 63);
    }

    /**
     * Retrives active grid
     * @return AW_Advancedreports_Block_Advanced_Grid
     */
    public function getGrid()
    {
        $this->_validateInstance();

        return $this->_grid;
    }

    /**
     * Check table existanse in database
     *
     * @param string $tableName
     * @return boolean
     */
    private function _tableExists($tableName)
    {        
        $read = $this->_getReadAdapter();
        $tableExistsSql = $this->_getReadAdapter()
            ->quoteInto("SHOW TABLE STATUS LIKE ?", $tableName);
        if ($read->fetchRow($tableExistsSql)) {
            return true;
        } else {
            return false;
        }        
    }

    /**
     * Create database for grid columns
     * @param string $name
     * @param AW_Advancedreports_Block_Advanced_Grid $grid
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    private function _createFlatTable($name, $grid)
    {        
        if ($grid){
            $write = $this->_getWriteAdapter()->beginTransaction();

            $lines = "";
            foreach ($grid->getSetupColumns() as $column){
                $index = $column->getIndex();
                $datetype = $column->getDatabaseType();
                $lines .=  "`{$index}` {$datetype} ,\n";
            }

            $periodKey = self::DATE_KEY_FIELD;
            $sql = "
            CREATE TABLE IF NOT EXISTS `{$name}`(
               `entity_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
               `{$periodKey}` datetime NOT NULL,
               {$lines}
               PRIMARY KEY (`entity_id`),
               INDEX (`{$periodKey}`)
             )  Engine=InnoDB charset=utf8;

            ";

            $write->query(new Zend_Db_Expr($sql));
            $write->commit();
        }        
        return $this;
    }

    /**
     * Truncate table with <code>$name</code>
     * 
     * @param string $name
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    private function _dropFlatTable($name, $connection)
    {
        if ($this->_tableExists($name)){
            try {
                $connection->exec(new Zend_Db_Expr("DROP TABLE `{$name}`"));
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Check aggregator table existanse and create it
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    private function checkATable()
    {
        $table = $this->getTableName();
        if (!$this->_tableExists($table)){
            $this->_createFlatTable($table, $this->getGrid());
        }
        return $this;
    }

    /**
     * Retrives helper
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    /**
     * Collection
     * @return AW_Advancedreports_Model_Mysql4_Aggregation_Collection
     */
    protected function _getAggrCollection()
    {
        return Mage::getModel('advancedreports/aggregation')->getCollection();
    }

    protected function _cleanExpiredInPeriod($from, $to)
    {
        ///TODO 

        return $this;
    }

    protected function _reaggregateRequired($from, $to)
    {
        $periods = array();

        # 1.  Cleaning expired data
        $this->_cleanExpiredInPeriod($from, $to);

        # 2. Searching of unindexed data
        $collection = $this->_getAggrCollection();
        $collection
            ->setPeriodFilter($from, $to)
            ->setTableFilter($this->getTableName())
            ->setExpiredFilter(AW_Advancedreports_Model_Aggregation::EXPIRED_FALSE)
            ->setToOrdering()
            ;

        if ($collection->getSize()){
            return $collection->reagregateRequired();         
        } else {
            return array(
                array('from' => $from, 'to'=>$to),
            );
        }
    }

    protected function _incSec($datetime)
    {
        $date = new Zend_Date(
                                $datetime,
                                null,
                                $this->_helper()->getLocale()->getLocaleCode()
                            );
        $date->addSecond(1);
        return $date->toString(self::MYSQL_ZEND_DATE_FORMAT);
    }

    protected function _decSec($datetime)
    {
        $date = new Zend_Date(
                                $datetime,
                                null,
                                $this->_helper()->getLocale()->getLocaleCode()
                            );
        $date->subSecond(1);
        return $date->toString(self::MYSQL_ZEND_DATE_FORMAT);
    }

    protected function _getPriodsToAggregate($from, $to)
    {
        # Aggreagate all
        return $this->_reaggregateRequired($from, $to);
    }

    /**
     * Setup aggregated collection
     * <ol>
     * <li>Create table if not exists</li>
     * <li>Check periods to aggregate</li>
     * <li>Aggregate them</li>
     * <li>Set up collection</li>
     * </ol>
     *
     * @param datetime $from
     * @param datetime $to
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function prepareAggregatedCollection($from, $to)
    {               
        $this->_validateInstance();

        # 1. Creating table if not exists
        $this->checkATable();
        
        
        # 2. Getting period to aggregate
        $periods = $this->_getPriodsToAggregate($from, $to);
               
        # 3. Aggreagating them
        foreach ($periods as $period){
            try {

                if (!isset($period['today']) || !$period['today']){
                    $aggregating = Mage::getModel('advancedreports/aggregation')
                        ->setFrom($period['from'])
                        ->setTo($period['to'])
                        ->setTable($this->getTableName())
                        ->setData('timetype',$this->_timeType)
                        ;

                }                

                $this->_aggregateData($period['from'], $period['to']);

                if (!isset($period['today']) || !$period['today']){
                    $aggregating->save();
                }

            } catch (Exception $e) {                 
                Mage::logException($e);
                return $this;
            }
        }

        # 4. Set up aggreagted collection
        $collection = Mage::getModel('advancedreports/cache')->getCollection();
        $collection->setPeriodFilter($from, $to);
        $collection->setMainTable($this->getTableName());

        $this->_collection = $collection;
        return $this;
    }

    protected function _getDataKeyColumn()
    {
        foreach ($this->getGrid()->getSetupColumns() as $column){
            if ($column->getIsPeriodKey()){
                return $column->getIndex();
            }
        }        
        return null;
    }

    /**
     * Get data from  report and write them to
     *
     * @param datetime $from
     * @param datetime $to
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    protected function _aggregateData($from, $to)
    {
        $data = $this->getGrid()->getPreparedData($from, $to);

        $table = $this->getTableName();
        $dateKey = $this->_getDataKeyColumn();
        $columns = $this->getGrid()->getSetupColumns();
        $periodKeyField = self::DATE_KEY_FIELD;

        $keys = array(self::DATE_KEY_FIELD);
        foreach ($columns as $column){
            $keys[] = $column->getIndex();
        }

        $this->_getWriteAdapter()->beginTransaction();

        $query = "DELETE FROM `{$table}` WHERE (`{$periodKeyField}` >= '{$from}') AND (`{$periodKeyField}` <= '{$to}');\n";
        $this->_getWriteAdapter()->query(new Zend_Db_Expr($query));

        if ($data){
            foreach ($data as $row){
                $values = array( $row[$dateKey] );
                foreach ($columns as $column){
                    $values[] = ($column->getType() == 'text') ? str_replace("'", "''", $row[$column->getIndex()])  : $row[$column->getIndex()];
                }
                $query = "INSERT INTO `{$table}` (`".  implode("`,`", $keys)."`) VALUES ('".  implode("','", $values)."');\n";
                $this->_getWriteAdapter()->query(new Zend_Db_Expr($query));
            }
        }
           
        $this->_getWriteAdapter()->commit();            

        return $this;
    }

    /**
     * Retrives collection with cached data
     * 
     * @return AW_Advancedreports_Model_Mysql4_Cache_Collection
     */
    public function getAggregatetCollection()
    {
        return $this->_collection;
    }

    /**
     * Completelly remove cache
     * 
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function cleanCache()
    {
        $write = $this->_getWriteAdapter()->beginTransaction();
        $aggrs = Mage::getModel('advancedreports/aggregation')->getCollection();             
        foreach ($aggrs->getAllTables() as $table){
            $this->_dropFlatTable($table['table'], $write);
        }
        $write->commit();
        $aggrs->clearTable();
        return $this;
    }
}