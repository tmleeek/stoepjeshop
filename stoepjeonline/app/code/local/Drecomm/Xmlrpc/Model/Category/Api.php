<?php

/**
 *
 * @author Sergey Gozhedrianov
 *
 */
class Drecomm_Xmlrpc_Model_Category_Api extends Mage_Catalog_Model_Category_Api {

    /**
     * @var object database connection
     */
    private $_oConnection;

    /**
     * Description
     * @var string
     */
    private $_fastimport_categorytable;

    /**
     * Unique identifier for a category position
     * Will be autoincremented when the next call is invoked
     * @var int
     */
    private $_category_postion;


    private $_category_glow;

        /**
     * Construct class
     *
     * @return Drecomm_Xmlrpc_Model_Category_Api
     */
    function __construct () {

        parent::__construct();
        /**
         * Set defaults
         */
        $this->_fastimport_categorytable    = 'fastimport_categories';
        $this->_category_postion            = 1;

        $this->_category_glow               = '|';

        return $this;
    }


    /**
     * Connect to local database
     *
     * @return void
     */
    private function _initConnection() {
        // Setup db connection
        $this->_oConnection = Mage::getSingleton('core/resource')->getConnection('catalog_write');
    }


    /**
     * Initilize and return category model
     *
     * @param array $categoryData
     * @param string|int $store
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory($categoryData, $store = null, $isParent = false)
    {
        if (is_array($categoryData)) {
            $category = Mage::getModel('catalog/category')
                ->setStoreId($this->_getStoreId($store))
                ->loadCategoryByAvId($categoryData['key_value'], $categoryData['key_name']);
        } elseif ($isParent){
            $rootCategoryId = Mage::app()->getStore($this->_getStoreId($store))->getRootCategoryId();
            $category = Mage::getModel('catalog/category')->load($rootCategoryId);
        }
        return $category;
    }

    /**
     * Create new category
     *
     * @param array $categoryData
     * @param null  $ignoredParam1
     * @param null  $ignoredParam2
     * @return int
     */
    public function create($categoryData, $ignoredParam1, $ignoredParam2=null)
    {


        if (!isset($categoryData['name']) || !isset($categoryData['parent']) || !isset($categoryData['category_id'])) {
            $this->_fault('data_invalid'. "Set category_id, parent and name");
        }

        $store = $categoryData['store_id'];
        unset($categoryData['store_id']);
        $parent_category = $this->_initCategory($categoryData['parent'], $store, false);
        if (!$parent_category || !$parent_category->getId()) {
            $this->_fault("Given parent category does not exist: ".implode(':', $categoryData['parent']));
        }
        unset($categoryData['parent']);
        $isUpdate = true;
        $category = $this->_initCategory($categoryData['category_id'], $store);
        if (!$category) {
            $isUpdate = false;
            $category = Mage::getModel('catalog/category')->setStoreId($this->_getStoreId($store));
            $categoryData[$categoryData['category_id']['key_name']] = $categoryData['category_id']['key_value'];
            $category->addData(array('path'=>implode('/',$parent_category->getPathIds())));
        } else {
            if ($parent_category->getId() != $category->getParentId()) {
                //moving category
                try{
                  $category->move($parent_category->getId(), 0);
                  //reload category model
                  $category = $this->_initCategory($categoryData['category_id'], $store);
                } catch (Exception $e) {
                    $this->_fault('data_invalid'.$e->getMessage());
                }
            }
        }
        unset($categoryData['category_id']);

        if (isset($categoryData['image']) && filter_var($categoryData['image'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            // Only download image when it is an URL. If it is set, but not an URL, just let it pass
            $categoryData['image'] = $this->_downloadImage($categoryData['image'], $category->getImage());
        }

        /* @var $category Mage_Catalog_Model_Category */
        $category ->setAttributeSetId($category->getDefaultAttributeSetId());

        foreach ($category->getAttributes() as $attribute) {
            if ($this->_isAllowedAttribute($attribute)
                && isset($categoryData[$attribute->getAttributeCode()])) {
                $category->setData(
                    $attribute->getAttributeCode(),
                    $categoryData[$attribute->getAttributeCode()]
                );
            }
        }

        try {
            $validate = $category->validate();
            if ($validate !== true) {
                foreach ($validate as $code => $error) {
                    if ($error === true) {
                        Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $code));
                    }
                    else {
                        Mage::throwException($error);
                    }
                }
            }
            $category->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid'.$e->getMessage());
        }
        if ($isUpdate) {
            $responseMessage = "Category Updated succesfully";
        } else {
            $responseMessage = $category->getId();
        }
        $responseObject = new Zend_XmlRpc_Response($responseMessage);
        return $responseObject;
    }

    public function _downloadImage($imageUrl, $imageFileName = null)
    {
        $httpClient = new Zend_Http_Client();
        $url = parse_url($imageUrl);
        $fileName = pathinfo($url['path'], PATHINFO_BASENAME);
        $response = $httpClient->setUri($imageUrl)->request('GET');
        $categoryImageDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
        try {
            $ioAdapter = new Varien_Io_File();
            if ($imageFileName && is_readable($categoryImageDir . $imageFileName))
            {
                $ioAdapter->rm($categoryImageDir . $imageFileName);
            }
            $fileName = Varien_File_Uploader::getNewFileName($categoryImageDir . $fileName);
            $ioAdapter->CheckAndCreateFolder($categoryImageDir);
            $ioAdapter->open(array('path' => $categoryImageDir));
            $ioAdapter->write($fileName, $response->getRawBody(), 0664);

        } catch (Exception $e){
            $this->_fault('Error downloading category image ' . $e->getMessage());
        }
        return $fileName;
    }

    /**
     * Delete category by given param
     * @param array $categoryData
     */
    public function delete($categoryData) {
        $category = $this->_initCategory($categoryData['category_id']);
        if (!$category) {
            $this->_fault('not_exists');
        }
        try {
            $category->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted'. $e->getMessage());
        }

        $responseObject = new Zend_XmlRpc_Response("Deleted succesfully");
        return $responseObject;
    }

    /**
     * The data needed by the original rpc call
     *
     * @param   array   $category_row
     * @return  array   Data needed for original rpc call
     */
    private function _expectedData ( $category_row ) {


        /**
         * break-up parent
         */
        if ( isset ($category_row['parent']) ) {

            if (($parent = unserialize($category_row['parent']))) {
                $category_row['parent'] = $parent;

            } else if ($category_row['parent']) {
                list ($key, $value) = explode($this->_category_glow, $category_row['parent'], 2);
                $category_row['parent'] = array(
                   'key_name'           => $key,
                   'key_value'          => $value,
                );
            } else {
                // create will throw a valid error because the parent is missing
                unset ($category_row['parent']);
            }
        }

        /**
         * break-up category_id
         */
        if ( isset ($category_row['category_id']) ) {

            if (($parent = unserialize($category_row['category_id']))) {
                $category_row['category_id'] = $parent;

            } else if ($category_row['category_id']) {
                list ($key, $value) = explode($this->_category_glow, $category_row['category_id'], 2);
                $category_row['category_id'] = array(
                   'key_name'           => $key,
                   'key_value'          => $value,
                );
            } else {
                // create will throw a valid error because the parent is missing
                unset ($category_row['category_id']);
            }
        }

        /**
         * is active must be form type bool
         */
        if ( isset ($category_row['is_active']) ) {
            // Convert to boolean
            $category_row['is_active']=$category_row['is_active']?true:false;
        }

        return $category_row;
    }


    /**
     * Start serverside data for importing data
     *
     * @param array $categoryData
     */
    public function createfast( $categoryData = array () ) {

        /**
         * Start the script serverside
         */
 $this->createfast_worker($categoryData);

//        shell_exec( 'nohup php ./import/categoryImport.php >/dev/null 2>&1 &' );

        return new Zend_XmlRpc_Response('Started script serverside');

    }

    /**
     * Read a static table and send the data to $this->create, a real fast way of
     * parsing categories one call to write all categories
     *
     * @param array $categoryData dummy
     * @return object Zend_XmlRpc_Response("response");
     */
    public function createfast_worker ( $categoryData = array () ) {

        $_message = 'Just started importing...';


        /**
         * Create local connection
         */
        $this->_initConnection ();

        /**
         * Select category data from import table
         */
        $oSelect = $this->_oConnection->select();
        $oSelect->from(
                $this->_fastimport_categorytable ,
                array ( '*' )
            )->order('level')
            ->order('position')
            ->order('category_id')/*
            ->limit(1)*/;

        $category_result = $this->_oConnection->fetchAll($oSelect);


        // If there are no results return message
        if ( empty ( $category_result ) ) {
            $_message = 'Nothing to be done, no categories found.';
        } else {

            /**
             * Walk results, send them to original function
             */
            foreach ( $category_result as $category_row ) {


                /**
                 * Merge data from database with default data
                 */
                $category_row = $this->_expectedData($category_row);

                /**
                 * Try to create or update caetgory
                 */

                try {
                    $this->create( $category_row );
                } catch ( Mage_Api_Exception $e ) {

                    $this->_fault(__METHOD__.': Error occured when importing category "'.$category_row['name'].'" '.$e->getMessage());
                }
            }

            /**
             * Succesfully imported
             */
            $_message = 'Import was succesfull';
            Mage::log('Import was succesfull',NULL, 'talend.log', true);
        }


        return new Zend_XmlRpc_Response($_message);
    }
}
