<?php
/**
 * Catalog product api
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Drecomm_Xmlrpc_Model_Product_Api extends Mage_Catalog_Model_Product_Api
{

    const MEDIA_ATTRIBUTE_CODE = 'media_gallery';

    private $_fastimport_producttable;

    private $_oConnection;

    protected $_filtersMap = array(
        'product_id' => 'entity_id',
        'set'        => 'attribute_set_id',
        'type'       => 'type_id'
    );

    public function __construct()
    {
        parent::__construct();

        $this->_fastimport_producttable = 'fastimport_products';
        $this->_storeIdSessionField = 'product_store_id';
        $this->_ignoredAttributeTypes[] = 'gallery';
        $this->_ignoredAttributeTypes[] = 'media_image';
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param array $filters
     * @param string|int $store
     * @return array
     */
    public function items($filters = null, $store = null)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($this->_getStoreId($store))
            ->addAttributeToSelect('name');

        $collection->getSelect()->limit(200);
        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_filtersMap[$field])) {
                        $field = $this->_filtersMap[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $product) {
//            $result[] = $product->getData();
            $result[] = array( // Basic product data
                'product_id' => $product->getId(),
                'sku'        => $product->getSku(),
                'name'       => $product->getName() ? $product->getName() : 'not set',
                'set'        => $product->getAttributeSetId(),
                'type'       => $product->getTypeId()
                //'category_ids'       => $product->getCategoryIds()
            );
        }

        return $result;
    }

    /**
     * Retrieve product info
     *
     * @param int|string $productId
     * @param string|int $store
     * @param array $attributes
     * @return array
     */
    public function info($productId, $store = null, $attributes = null, $identifierType = null)
    {
        $product = $this->_getProduct($productId, $store, $identifierType);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        $result = array( // Basic product data
            'product_id' => $product->getId(),
            'sku'        => $product->getSku(),
            'set'        => $product->getAttributeSetId(),
            'type'       => $product->getTypeId(),
            'categories' => $product->getCategoryIds(),
            'websites'   => $product->getWebsiteIds()
        );

        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute, $attributes)) {
                $result[$attribute->getAttributeCode()] = $product->getData(
                                                                $attribute->getAttributeCode());
            }
        }

        return $result;
    }

    /**
     * Transform array of category names to category ids
     * @param array $categoryDataArray
     * @return array
     */
    protected function _assembleCategories($categoryDataArray)
    {
        $resultArray = array();
        foreach($categoryDataArray as $attributeKey => $dataArray) {
            foreach($dataArray as $categoryId) {
                $category = Mage::getModel('catalog/category')->loadCategoryByAvId($categoryId, $attributeKey);
                if ($category && $category->getId()) {
                 $resultArray[] = $category->getId();
                } else {
                    $this->_fault('data_invalid', 'Category not found ('.$attributeKey.': '.$categoryId.')');
                }
            }
        }
        return $resultArray;
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

    private function inAction($product) {
        $from_date = strtotime($product['special_from_date']);
        $to_date   = strtotime($product['special_to_date']);
        $date = strtotime("now");

        if($date > $from_date && $date < $to_date) return true;
        return false;
    }


    public function createfast($productData = array(), $set=false, $sku=false, $productData2=false, $store = null)
    {
        /**
         * Create script
         */
        $phpCode = '<?php
            require_once(\'app/Mage.php\');
            \$app = Mage::app();

            Mage::app()->setCurrentStore(\'admin\');

            \$cmc = Mage::getModel(\'catalog/product_api\');
            \$cmc->createfast_worker();
            ?>';
        $cmd = 'nohup echo "'.$phpCode.'" | php > /dev/null 2>&1 &';

        // Execute
        shell_exec($cmd);

        return new Zend_XmlRpc_Response('Started script serverside');
    }

    public function createfast_worker()
    {
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
                $this->_fastimport_producttable ,
                array ( '*' )
            )->order('categories');

        $product_result = $this->_oConnection->fetchAll($oSelect);
        $category = null;
        foreach($product_result as $productDB)
        {
            if ( $category === null || $category->getProductGroupAvId() != $productDB['categories'] ) {
                $category = Mage::getModel('catalog/category')->loadCategoryByAvId($productDB['categories'], 'product_group_av_id');
            }
            unset($productDB['categories']);
            $product = $productDB;
            $product['categories'][] = $category->getEntityId();


            if($this->inAction($productDB))
            {
                 $product['categories'][] = 52;
            }

            $this->create($product);
            unset($product);
        }
        return new Zend_XmlRpc_Response($_message);
    }

    /**
     * Create new product.
     *
     * @param array $productData
     * following params are added to avoid php errors, and have been named to the original param name to prevent E_STRICT warnings
     * @param null $redundantOne
     * @param null $redundantTwo
     * @param null $redundantThree
     * @param array $customOptions
     *
     * @return Zend_XmlRpc_Response object
     */
    public function create($productData, $set=false, $sku=false, $productData2=false, $store = null)
    {
        $set = isset($productData['attribute_set_id']) ? $productData['attribute_set_id'] : null ;
        $type = isset($productData['type']) ? $productData['type'] : null;
        $sku = isset($productData['sku']) ? $productData['sku'] : null;
        $productData = is_array($productData) ? $productData : $productData2;

        if (!$type || !$set || !$sku) {
            $this->_fault('data_invalid', "Please specify product type, attribute_set_id and sku");
        }

//        if (isset($productData['categories'])) {
//            $categoryIds = $this->_assembleCategories($productData['categories']);
//            $productData['category_ids'] = $categoryIds;
//            unset($productData['categories']);
//        }
//
        //$product = $this->_getProduct($sku, $this->_getStoreId(), 'sku');
        $product = $this->_getProduct($sku, $productData['store_id'], 'sku');

        $isUpdate = true;
        if (!$product->getId()) {
            $isUpdate = false;
            $product = Mage::getModel('catalog/product');
        }

        /* @var $product Mage_Catalog_Model_Product */
        $product->setStoreId($productData['store_id'])
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);

        try {
            if (!$isUpdate && (isset($productData['images']) || isset($productData['gallery']))) {
                if (isset($productData['images']) && !empty($productData['images']) && preg_match("/(jpe?g|png|gif)$/",$productData['images'])) {
                    $this->_addImage($product, $productData['images'], $productData['name']);
                }

                if (isset($productData['gallery']) && !empty($productData['gallery']) && preg_match("/(jpe?g|png|gif)$/",$productData['gallery'])) {
                    $this->_addGalleryImage($product, $productData['gallery'], $productData['name']);
                }
            }
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        if (isset($productData['website_ids']) && !is_array($productData['website_ids'])) {
            $productData['website_ids'] = explode(',', $productData['website_ids']);
        }
        if (isset($productData['website_ids']) && is_array($productData['website_ids'])) {
            $product->setWebsiteIds($productData['website_ids']);
        }

        if (isset($productData['related_products']) && (is_array($productData['related_products']) || trim($productData['related_products']))) {
            $this->_setRelatedProducts($product, 'related', $productData['related_products']);
            unset($productData['related_products']);
        }
        if (isset($productData['up_sell_products']) && (is_array($productData['up_sell_products']) || trim($productData['up_sell_products']))) {
            $this->_setRelatedProducts($product, 'up_sell', $productData['up_sell_products']);
            unset($productData['up_sell_products']);
        }
        if (isset($productData['cross_sell_products']) && (is_array($productData['cross_sell_products']) || trim($productData['cross_sell_products']))) {
            $this->_setRelatedProducts($product, 'cross_sell', $productData['cross_sell_products']);
            unset($productData['cross_sell_products']);
        }
        if (isset($productData['grouped_products']) && (is_array($productData['grouped_products']) || trim($productData['grouped_products']))) {
            $this->_setRelatedProducts($product, 'grouped', $productData['grouped_products']);
            unset($productData['grouped_products']);
        }
        if(isset($productData['configurable_products_data']) && is_string($productData['configurable_products_data']) && isset($productData['configurable_attributes_data']) && is_string($productData['configurable_attributes_data'])) {

			/*
			$product->setConfigurableProductsData(
				array(
					array(
						'attribute_id' => 1,
						'position' => 0,
						'label' => 'Select color',
						'values' => array (
										'value_index' => 123,
										'is_percent' => 1,
										'pricing_value' => 20.00
									)
					)
				)
			);

			$product->setConfigurableProductsData(array(
        		1 => 1
        		2 => 2
        		3 => 3
			));*/

			$arrayProducts = explode(',',$productData['configurable_products_data']);

			//All attributes which should be configurable
			$arrayAttributen = explode(',',$productData['configurable_attributes_data']);

			$arrayAttr = array();
			$productIDS = array();
			$i = 0;
			foreach ($arrayAttributen as $akey => $attrName) {

				$attrData = Mage::getResourceModel('eav/entity_attribute_collection')
						->setCodeFilter($attrName)
						->getFirstItem();


				$arrayAttr[$i]['attribute_id'] = $attrData->getAttributeId();
				$arrayAttr[$i]['position'] = 0;
				$arrayAttr[$i]['label'] = $attrData->getFrontend()->getLabel();
				$ii = 0;
				foreach ($arrayProducts as $pKey => $sku) {

					$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
					//Disable simple product catalog visibility
					$_product->setVisibility(3);
					$_product->save();

					$productIDS[$_product->getId()] = $_product->getId();
					// get the attribute object
					$dropdownAttributeObj = $_product->getResource()->getAttribute($attrData->getAttributeCode());

					if ($dropdownAttributeObj->usesSource()) {
						$dropdown_option_id = $dropdownAttributeObj->getSource()->getOptionId($_product->getData($attrData->getAttributeCode()));
					} else {
						$dropdown_option_id = 1;
					}
					$arrayAttr[$i]['values'][$ii]['value_index'] = $dropdown_option_id; // Rood?
					$arrayAttr[$i]['values'][$ii]['is_percent'] = 1;
					$arrayAttr[$i]['values'][$ii]['pricing_value'] = 0;

					//This var is needed for the configurable price below
					$configSimpleSku = $sku;

					$ii++;
				}
				$i++;
			}

			$_checkConfig = Mage::getModel('catalog/product')->loadByAttribute('sku',$productData['sku']);

			if (!$_checkConfig) {
				$product->setConfigurableAttributesData($arrayAttr);
			}
			$product->setConfigurableProductsData($productIDS);
		}

        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute) && isset($productData[$attribute->getAttributeCode()])) {
                    if ($attribute->getFrontendInput() == 'select') {
                        if ($attribute->getSourceModel() !== 'eav/entity_attribute_source_table') {
                            $attributeValue = $productData[$attribute->getAttributeCode()];
                        } else {
                            $model = Mage::getModel('catalog/resource_eav_attribute')->load($attribute->getId());
                            $attributeValue = $model->getSource()->getOptionId($productData[$model->getAttributeCode()]);
                            if ($attributeValue === null) {
                                $this->_addNewAttributeValue($model, $productData[$attribute->getAttributeCode()]);

                                //reload attribute model to fetch new values from db
                                $model = Mage::getModel('catalog/resource_eav_attribute')->load($attribute->getId());
                                $attributeValue = $model->getSource()->getOptionId($productData[$model->getAttributeCode()]);
                            }
                        }
                    } else {
                    	//IF loop will check if the product is a configurable. If yes then it will take the price of the last associated simple product.
                    	if ($productData['type'] == 'configurable' && $attribute->getAttributeCode() == 'price') {
                    		$_simple = Mage::getModel('catalog/product')->loadByAttribute('sku',$configSimpleSku);
                    		$attributeValue = $_simple->getPrice();
                    	} else {
                       		$attributeValue = $productData[$attribute->getAttributeCode()];
                       	}
                    }
                $product->setData(
                    $attribute->getAttributeCode(),
                    $attributeValue
                );

            }
        }

        $this->_prepareDataForSave($product, $productData);

        if (is_array($errors = $product->validate())) {
            $this->_fault('data_invalid', implode("\n", $errors));
        }

        $stockData = $product->getStockData();
        $stockData['min_sale_qty'] = 1;
        $stockData['max_sale_qty'] = 1000;
        $stockData['use_config_manage_stock'] = 0;
        $stockData['manage_stock'] = 0;
        $stockData['is_in_stock'] = 1;
        if (isset($productData['use_config_qty_increments']))     { $stockData['use_config_qty_increments']     = $productData['use_config_qty_increments']; }
        if (isset($productData['qty_increments']))                { $stockData['qty_increments']                = $productData['qty_increments']; }
        if (isset($productData['stock_manage_stock']))            { $stockData['manage_stock']                  = $productData['stock_manage_stock']; }
        if (isset($productData['stock_use_config_manage_stock'])) { $stockData['stock_use_config_manage_stock'] = $productData['stock_use_config_manage_stock']; }
        $product->setStockData($stockData);

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('exception', $e->getMessage());
        }

        if (isset($productData['customOptions']) && is_array($productData['customOptions'])) {
			//Custom options structure
			/**
	 		* $customOptions[]['value'] = Value(s) of the option, must be comma seperated
	 		* $customOptions[]['title'] = Title of the option
	 		* $customOptions[]['type'] = Type: drop_down,radio,checkbox,multiple,area,field
	 		* $customOptions[]['hasoptions'] = If the custom option has actual options, false for text fields for example
	 		*
	 		* For Creating dropdown,select,multiselect,radio type of custom option
			* $arrayOption[] = setCustomOption("OPT1,OPT2", "Select Option", "drop_down");
			*
	 		* For Creating textfield and textarea type of custom option
			* $arrayOption[] = setCustomOption("Anyvalue", "Area", "area", true);
			**/
	 		$arrayOption = array();

	 		foreach ($productData['customOptions'] as $customOption) {
	 			$arrayOption[] = $this->setCustomOption($customOption['value'], $customOption['title'], $customOption['type'], $customOption['hasoptions']);
	 		}

			//$this->_fault('data_invalid', print_r($arrayOption,true));

			foreach ($arrayOption as $options) {
				foreach ($options as $option) {
					$opt = Mage::getModel('catalog/product_option');
					$opt->setProduct($product);
					$opt->addOption($option);
					$opt->saveOptions();
					$product->setData($product->getData())->save();
				}
			}
		}

        if ($isUpdate) {
            $responseMessage = "Update succesfull";
        } else {
            $responseMessage = $product->getId();
        }
        $responseObject = new Zend_XmlRpc_Response($responseMessage);

        return $responseObject;
    }


	/**
	 *
	 * @param Mage_Catalog_Model_Product $productObject
	 * @param string $type One of: cross_sell, up_sell, related, grouped; default: related
	 * @param array $relatedProducts Array of SKUs
	 */
	protected function _setRelatedProducts(Mage_Catalog_Model_Product &$product, $type, $newRelated) {
		$store = $product->getStoreId();
		if (!is_array($newRelated)) {
			$newRelated = explode(',', $newRelated);
		}
		$relatedData = array();
		foreach ($newRelated as $position=>$sku) {
			$relId = $this->_getProduct($sku, $store, 'sku')->getId();
			if ($relId) {
				$relatedData[$relId]['position'] = $position;
			}
		}

		// Set and save related product data
		switch($type) {
			case 'cross_sell':
				$product->setCrossSellLinkData($relatedData);
				break;
			case 'up_sell':
				$product->setUpSellLinkData($relatedData);
				break;
			case 'grouped':
				$product->setGroupedLinkData($relatedData);
				break;
			case 'related':
			default:
				$product->setRelatedLinkData($relatedData);
		}
	}


    /**
     * Add new option to select attribute
     * @param Mage_Catalog_Model_Resource_Attribute $model
     * @param string $value
     */
    protected function _addNewAttributeValue($model, $value)
    {
        if (!$model->getId()) {
            $this->_fault('data_invalid', "Unknown product attribute");
        }

        $data['attribute_code'] = $model->getAttributeCode();
        $data['is_user_defined'] = $model->getIsUserDefined();
        $data['frontend_input'] = $model->getFrontendInput();
        $data['is_configurable'] = $model->getData('is_configurable');
        $data['is_filterable'] = $model->getData('is_filterable');
        $data['is_filterable_in_search'] = $model->getData('is_filterable_in_search');

        $data['backend_type'] = $model->getBackendTypeByInput($model->getFrontendInput());

        if(!isset($data['apply_to'])) {
            $data['apply_to'] = array('simple');
        }
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($model->getId())
                ->setPositionOrder('desc', true)
                ->load();
        $optionCount = sizeof($optionCollection);

        $storeCollection = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
        $option = array();
        foreach($storeCollection as $store)
        {
            $option['value']['option_' . $optionCount][$store->getId()] = $value;
        }
        $option['order']['option_' . $optionCount] = 1;
        $data['option'] = $option;
        $model->addData($data);
        try {
            $model->save();
        } catch(Exception $e) {
            Mage::log($e->getMessage());
            $this->_fault('data_invalid', "Cannot create new option to attribute");
        }
        unset($model);
    }

	/**
	 * Checks whether an image URL is valid
	 *
	 * @param	string	$url
	 * @return	boolean
	 */
	protected function isImageUrlValid($url) {
		return preg_match('/\.(gif|jpg|jpeg|png)$/i', $url);
	}

    /**
     * Add image to given product
     * @param Mage_Catalog_Model_Product $productObject
     * @param string $imageUrl
     * @param string $productName (for pic label)
     */
    protected function _addImage($productObject, $imageUrl, $productName)
    {
		// @TODO: when updating, only the meta data of an image is updated. That means that
		// when the source image changes, but the location remains the same, the image is
		// not updated in Magento. Also applicable for self::_addGalleryImage()
		if (!$this->isImageUrlValid($imageUrl)) {
			throw new Exception('Base-image type is invalid: '.$imageUrl);
		}
        $httpClient = new Zend_Http_Client();
        $url = parse_url($imageUrl);
        $path = pathinfo($url['path']);
        $fileName = md5($imageUrl . $productObject->getId()).'-'.$path['basename'];
        $response = $httpClient->setUri($imageUrl)->request('GET');
        $tmpDirectory = Mage::getBaseDir('var') . DS . 'drecomm' . DS . $this->_getSession()->getSessionId();
        $ioAdapter = new Varien_Io_File();
        try{
            $ioAdapter->CheckAndCreateFolder($tmpDirectory);
            $ioAdapter->open(array('path' => $tmpDirectory));
            $ioAdapter->write($fileName, $response->getRawBody(), 0664); // Must be octal notation
            $filePath = $tmpDirectory . DS . $fileName;

            $dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
            $fileName = $dispretionPath . DS . Varien_File_Uploader::getNewFileName(Mage::getSingleton('catalog/product_media_config')->getTmpMediaPath($fileName));
            $nameToUpdate = str_replace("\\", "/", $fileName);
            $realPathToFile = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath() . $fileName;

            $gallery = $this->_getGalleryAttribute($productObject);
            $imageFile = $gallery->getBackend()->getImage($productObject, $nameToUpdate);
            if ($imageFile) {
				if ($imageFile['removed']) {
					$this->_cancelGalleryImageRemove($productObject, $imageFile);
				}
				$gallery->getBackend()->updateImage($productObject, $nameToUpdate, array('label' => $productName, 'exclude' => 1));
				$gallery->getBackend()->setMediaAttribute($productObject, array('image', 'small_image', 'thumbnail'), $nameToUpdate);
            } else {
                    if (is_readable($realPathToFile)) {
                      $ioObject = new Varien_Io_File();
                      $ioObject->rm($realPathToFile);
                    }
                $file = $gallery->getBackend()->addImage(
                    $productObject,
                    $filePath,
                    array('image', 'small_image', 'thumbnail'),
					false,
                    true
                );
				$gallery->getBackend()->updateImage($productObject, $file, array('label' => $productName));
            }
            $ioAdapter->rmdir($tmpDirectory, true);

        } catch (Mage_Core_Exception $e ) {
            $this->_fault('not_created', $e->getMessage());
        }
    }

    protected function _removeGalleryImages($product, $typesToRemove)
    {
		// @TODO: better image handling. For now we assume the image is disabled, gallery images are assumed to be enabled

        foreach($product->getMediaGallery('images') as $image) {
			if ($image['disabled']  && !$typesToRemove['images']) {
				continue;
			}
            if (!$image['disabled'] && !$typesToRemove['gallery']) {
                continue;
            }
            $this->_getGalleryAttribute($product)->getBackend()->removeImage($product, $image['file']);
        }
    }

    protected function _cancelGalleryImageRemove($product, $imageArray)
    {
        $productMediaGallery = $product->getMediaGallery();
        foreach ($productMediaGallery['images'] as &$image) {
            if ($image['file'] == $imageArray['file']) {
                $image['removed'] = 0;
            }
        }

        $product->setData('media_gallery', $productMediaGallery);

        return $this;
    }

    /**
     * Add image to product gallery
     * @param Mage_Catalog_Model_Product $productObject
     * @param string $imageUrl
     * @param string $productName (for pic label)
     */
    protected function _addGalleryImage($productObject, $imageUrlArray, $productName)
    {
        foreach($imageUrlArray as $imageUrl) {
			if (!$this->isImageUrlValid($imageUrl)) {
				 throw new Exception('Gallery-image type is invalid: '.$imageUrl);
			}
			//$this->_fault('data_invalid', "Please specify product type, attribute_set_id and sku" . $imageUrl);
			//return;
            $httpClient = new Zend_Http_Client();
            $url = parse_url($imageUrl);
            $path = pathinfo($url['path']);
			$fileName = md5($imageUrl . $productObject->getId()).'-'.$path['basename'];
            $response = $httpClient->setUri($imageUrl)->request('GET');
            $tmpDirectory = Mage::getBaseDir('var') . DS . 'drecomm' . DS . $this->_getSession()->getSessionId();
            $ioAdapter = new Varien_Io_File();
            try{
                $ioAdapter->CheckAndCreateFolder($tmpDirectory);
                $ioAdapter->open(array('path' => $tmpDirectory));
                $ioAdapter->write($fileName, $response->getRawBody(), 0664);
                $filePath = $tmpDirectory . DS . $fileName;

                $dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
                $fileName = $dispretionPath . DS . Varien_File_Uploader::getNewFileName(Mage::getSingleton('catalog/product_media_config')->getTmpMediaPath($fileName));
                $nameToUpdate = str_replace("\\", "/", $fileName);
                $realPathToFile = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath() . $fileName;

                $gallery = $this->_getGalleryAttribute($productObject);
                $imageFile = $gallery->getBackend()->getImage($productObject, $nameToUpdate);
                if ($imageFile) {
                    if ($imageFile['removed']) {
                        $this->_cancelGalleryImageRemove($productObject, $imageFile);
					}
                    $gallery->getBackend()->updateImage($productObject, $nameToUpdate, array('label' => $productName, 'exclude' => 0));
                } else {
                    if (is_readable($realPathToFile)) {
                      $ioObject = new Varien_Io_File();
                      $ioObject->rm($realPathToFile);
                    }
                    $file = $gallery->getBackend()->addImage(
                        $productObject,
                        $filePath,
                        null,
                        false,
                        false
                    );
					$gallery->getBackend()->updateImage($productObject, $nameToUpdate, array('label' => $productName));
                }
                $ioAdapter->rmdir($tmpDirectory, true);

            } catch (Mage_Core_Exception $e ) {
                $this->_fault('not_created', $e->getMessage());
            }
        }
    }

    /**
     * Get media attribute for given product
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _getGalleryAttribute($product)
    {
        $attributes = $product->getTypeInstance(true)
            ->getSetAttributes($product);

        if (!isset($attributes[self::MEDIA_ATTRIBUTE_CODE])) {
            $this->_fault('not_media');
        }

        return $attributes[self::MEDIA_ATTRIBUTE_CODE];
    }

    /**
     * Get product object by given key
	 *
     * @param string $productId product key
     * @param int $store
     * @param string $identifierType
	 * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct($productId, $store = null, $identifierType = null)
    {
		return parent::_getProduct($productId, $store, $identifierType);
    }

    /**
     * Update product data
     *
     * @param int|string $productId
     * @param array $productData
     * @param string|int $store
     * @return boolean
     */
    public function update($productId, $productData, $store = null, $identifierType = 'sku')
    {
        $product = $this->_getProduct($productId, $store, $identifierType);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        if (isset($productData['website_ids']) && is_array($productData['website_ids'])) {
            $product->setWebsiteIds($productData['website_ids']);
        }

        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute)
                && isset($productData[$attribute->getAttributeCode()])) {
                $product->setData(
                    $attribute->getAttributeCode(),
                    $productData[$attribute->getAttributeCode()]
                );
            }
        }

        $this->_prepareDataForSave($product, $productData);

        try {
            if (is_array($errors = $product->validate())) {
                $this->_fault('data_invalid', implode("\n", $errors));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    /**
     *  Set additional data before product saved
     *
     *  @param    Mage_Catalog_Model_Product $product
     *  @param    array $productData
     *  @return   object
     */
    protected function _prepareDataForSave ($product, $productData)
    {
        if (isset($productData['categories']) && is_array($productData['categories'])) {
            $product->setCategoryIds($productData['categories']);
        }

        if (isset($productData['websites']) && is_array($productData['websites'])) {
            foreach ($productData['websites'] as &$website) {
                if (is_string($website)) {
                    try {
                        $website = Mage::app()->getWebsite($website)->getId();
                    } catch (Exception $e) { }
                }
            }
            $product->setWebsiteIds($productData['websites']);
        }

        if (Mage::app()->isSingleStoreMode()) {
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        if (isset($productData['stock_data']) && is_array($productData['stock_data'])) {
            $product->setStockData($productData['stock_data']);
        }
    }

    /**
     * Update product special price
     *
     * @param int|string $productId
     * @param float $specialPrice
     * @param string $fromDate
     * @param string $toDate
     * @param string|int $store
     * @return boolean
     */
    public function setSpecialPrice($productId, $specialPrice = null, $fromDate = null, $toDate = null, $store = null)
    {
        return $this->update($productId, array(
            'special_price'     => $specialPrice,
            'special_from_date' => $fromDate,
            'special_to_date'   => $toDate
        ), $store);
    }

    /**
     * Retrieve product special price
     *
     * @param int|string $productId
     * @param string|int $store
     * @return array
     */
    public function getSpecialPrice($productId, $store = null)
    {
        return $this->info($productId, $store, array('special_price', 'special_from_date', 'special_to_date'));
    }

    /**
     * Delete product
     *
     * @param int|string $productId
     * @return boolean
     */
    public function delete($productId, $identifierType = 'sku')
    {
		$productId = $productId['sku'];

        $product = $this->_getProduct($productId, null, $identifierType);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $product->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        $responseObject = new Zend_XmlRpc_Response("Product removed");
        return $responseObject;
    }
   /**
     * Create link for the product (up-sell, cross-sell, related)
     *      requried $data['mode'] = "replace"|"update";
     *      requried $data['type'] = "up_sell"|"cross_sell"|"related"
     *      requried $data['source_product'] = array('key_name' => "sku", 'value' => array("sku1", "sku2"))
     *      requried $data['link_products'] = array('key_name' => array("sku1", "sku2"))
     * @param array $data
     * @return boolean
     */
	public function addlink($data) {

		if (empty($data['mode']) or empty($data['type'])  or empty($data['source_product']) or empty($data['link_products'])) {
			$this->_fault('data_invalid', 'Mode and type and source_products and linked_products are requried');
		}
		$key = $data['source_product']['key_name'];
		$key_value  = $data['source_product']['key_value'];

		$product = Mage::getModel('catalog/product');
		$product = $product->loadByAttribute($key, $key_value);

		if (!$product) {
			$this->_fault('data_invalid', 'Product with '.$key.' = '.$key_value .' not found');
		}
		$linkData = array();
		$i=0;
		if ($data['mode'] == 'update') {
			if ($product->getId()) {
				switch ($data['type']) {
				case 'up_sell':
					$collection = $product->getUpSellLinkCollection();
					break;
				case 'related':
					$collection = $product->getRelatedLinkCollection();
					break;
				case 'cross_sell':
					$collection = $product->getCrossSellLinkCollection();
					break;
				};
				foreach($collection as $productItem) {
					$linkData[$productItem->getLinkedProductId()] = array('position' => $productItem->getPosition());
					$i = $productItem->getPosition();
				};
			}
		}


		foreach ($data['link_products'] as $key => $values) {
			foreach ($values as $key_value) {
				$linkedProduct = Mage::getModel('catalog/product');
				$linkedProduct = $linkedProduct->loadByAttribute($key, $key_value);
				if ($linkedProduct) {
					$linkData[$linkedProduct->getId()] = array('position' => $i++);
				}
			}
		}

		switch ($data['type']) {
		case 'up_sell':
			$product->setUpSellLinkData($linkData);
			break;
		case 'related':
			$product->setRelatedLinkData($linkData);
			break;
		case 'cross_sell':
			$product->setCrossSellLinkData($linkData);
			break;
		};

		$product->save();

		$responseObject = new Zend_XmlRpc_Response("Success");
		return $responseObject;
	}

	/**
	 * @param $value - Must be comma seperated options.
	 * @param $title - Title of the custom option.
	 * @param $type - Type of custom option - drop_down,radio,checkbox,multiple,area,field.
	 * @param $noOption - Specifies if the custom options has options or not.
	 */
	function setCustomOption ($value, $title, $type, $noOption = false)
	{
		$custom_options = array();
		if ($type && $value != "" && $value) {
			$values = explode(',', $value);
			if (count($values)) {
				/**If the custom option has options*/
				if ($noOption != "0") {
					$is_required = 0;
					$sort_order = 0;
					$custom_options[] = array(
						'is_delete' => 0 , 'title' => $title , 'previous_group' => '' , 'previous_type' => '' , 'type' => $type , 'is_require' => $is_required , 'sort_order' => $sort_order , 'values' => array()
					);
					foreach ($values as $v) {
						$titleopt = ucfirst(trim($v));
						switch ($type) {
							case 'drop_down':
							case 'radio':
							case 'checkbox':
							case 'multiple':
							default:
								$title = ucfirst(trim($v));
								$custom_options[count($custom_options) - 1]['values'][] = array(
									'is_delete' => 0 , 'title' => $titleopt , 'option_type_id' => - 1 , 'price_type' => '' , 'price' => '0' , 'sku' => '' , 'sort_order' => ''
								);
							break;
						}
					}
					return $custom_options;
				}
				/**If the custom option doesn't have options | Case: area and field*/
				else {
					$is_required = 1;
					$sort_order = '';
					$custom_options[] = array(
						"is_delete" => 0 , "title" => $title , "previous_group" => "text" , "price_type" => 'fixed' , "price" => '0' , "type" => $type , "is_required" => $is_required
					);
					return $custom_options;
				}
			}
		}
		return false;
	}

} // Class Mage_Catalog_Model_Product_Api End
