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
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_MultiFees_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FEE_ENABLED = 'mageworx_sales/multifees/enabled';
    const XML_IMG_MAX_WIDTH = 'mageworx_sales/multifees/max_width';
    const XML_IMG_MAX_HEIGHT = 'mageworx_sales/multifees/max_height';
    const XML_CUSTOMER_MESSAGE = 'mageworx_sales/multifees/customer_message';
    const XML_ENABLE_CUSTOMER_MESSAGE = 'mageworx_sales/multifees/enable_customer_message';
    const XML_ENABLE_DATE = 'mageworx_sales/multifees/enable_date';
    const XML_DATE_TITLE = 'mageworx_sales/multifees/date_title';
    const XML_DISPLAY_TAX = 'mageworx_sales/multifees/display_tax';
    const XML_AUTOADD_TOTAL = 'mageworx_sales/multifees/autoadd_total';

    const XML_FEES_ENABLE_SHIPPING = 'mageworx_sales/multifees/enable_shipping';
    const XML_FEES_ENABLE_PAYMENT = 'mageworx_sales/multifees/enable_payment';

    const DEFAULT_IMG_SIZE = 70;

    const STATUS_VISIBLE = 1;
    const STATUS_HIDDEN = 2;

    const REQUIRED_YES = 1;
    const REQUIRED_NO = 2;

    const TYPE_DROP_DOWN = 1;
    const TYPE_RADIO_BUTTON = 2;
    const TYPE_CHECKBOX = 3;

    const CHECKOUT_TYPE_PAYMENT = 4;
    const CHECKOUT_TYPE_SHIPPING = 5;

    const PRICE_TYPE_FIXED = 'fixed';
    const PRICE_TYPE_PERCENT = 'percent';

    const IS_CHECKOUT_TYPE = 1;

    public function getStatusArray()
    {
        return array(
            self::STATUS_VISIBLE => $this->__('Active'),
            self::STATUS_HIDDEN => $this->__('Disabled'),
        );
    }

    public function getRequiredArray()
    {
        return array(
            self::REQUIRED_NO => $this->__('No'),
            self::REQUIRED_YES => $this->__('Yes'),
        );
    }

    public function getTypeArray($all = false, $onlyCheckout = false)
    {
        $array = array();
        if ($all === true || $onlyCheckout === true) {
            $array[self::CHECKOUT_TYPE_PAYMENT] = $this->__('Payment Fee');
            $array[self::CHECKOUT_TYPE_SHIPPING] = $this->__('Shipping Fee');
            if ($onlyCheckout === true) {
                return $array;
            }
        }
        $array[self::TYPE_DROP_DOWN] = $this->__('Drop-Down');
        $array[self::TYPE_RADIO_BUTTON] = $this->__('Radio Button');
        $array[self::TYPE_CHECKBOX] = $this->__('Checkbox');

        ksort($array);
        return $array;
    }

    public function getPriceTypeArray()
    {
        return array(
            self::PRICE_TYPE_FIXED => $this->__('Fixed'),
            self::PRICE_TYPE_PERCENT => $this->__('Percent'),
        );
    }

    public function isRequired($value)
    {
        if ($value == self::REQUIRED_YES) {
            return true;
        } else {
            return false;
        }
    }

    public function isDropDown($value)
    {
        if ($value == self::TYPE_DROP_DOWN) {
            return true;
        } else {
            return false;
        }
    }

    public function isRadioButton($value)
    {
        if ($value == self::TYPE_RADIO_BUTTON) {
            return true;
        } else {
            return false;
        }
    }

    public function isCheckbox($value)
    {
        if ($value == self::TYPE_CHECKBOX) {
            return true;
        } else {
            return false;
        }
    }

    public function isTypeFixed($value)
    {
        if ($value == self::PRICE_TYPE_FIXED) {
            return true;
        } else {
            return false;
        }
    }

    public function isEnablePayment()
    {
        return Mage::getStoreConfigFlag(self::XML_FEES_ENABLE_PAYMENT);
    }

    public function isEnableShipping()
    {
        return Mage::getStoreConfigFlag(self::XML_FEES_ENABLE_SHIPPING);
    }

    public function isEnableDate()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_DATE);
    }

    public function getDateTitle()
    {
        return Mage::getStoreConfig(self::XML_DATE_TITLE);
    }

    public function isAutoaddTotal()
    {
        return Mage::getStoreConfig(self::XML_AUTOADD_TOTAL);
    }

    public function isEnableCustomerMessage()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_CUSTOMER_MESSAGE);
    }

    public function getCustomerMessage()
    {
        return Mage::getStoreConfig(self::XML_CUSTOMER_MESSAGE);
    }

    public function getFormatPrice($price, $includeContainer = true, $convert = false)
    {
        $store = Mage::app()->getStore();
        if ($convert === true) {
            $price = $store->convertPrice($price);
        }
        return $store->formatPrice($price, $includeContainer);
    }

    public function isIncludingTax($store = null)
    {
        return (Mage::getStoreConfig(self::XML_DISPLAY_TAX, $store) == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX);
    }

    public function isOldVersion()
    {
        $res = false;
        if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
            $res = true;
        }
        return $res;
    }

    public function getInclTaxPrice($price)
    {
        if ($this->isIncludingTax()) {
            $percent = $this->getTaxPercent();
            if ($percent && $price) {
                $price += ( ($price * $percent) / 100);
            }
        }
        return $price;
    }

    public function getTaxPercent()
    {
        $percent = 0;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $taxClassId = (int) $quote->getCustomerTaxClassId();
        if ($taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(null, null, $taxClassId, Mage::app()->getStore());
            $taxClass = Mage::getSingleton('tax/calculation')->getRatesByCustomerTaxClass($taxClassId);

            if ($taxClass) {
                $productTaxIds = $this->getProductTaxClassIds();
                $region = Mage::getSingleton('directory/region')->load($request->getRegionId());
                foreach ($taxClass as $value) {
                    if (($request->getCountryId() == $value['country'])
                            && ($region->getCode() == $value['state'])
                            && ($request->getPostcode() == $value['postcode'] || $value['postcode'] == '*')
                            && (in_array($value['product_class'], $productTaxIds))) {

                        $request->setProductClassId($value['product_class']);
                        $percent += Mage::getSingleton('tax/calculation')->getRate($request);
                    }
                }
            }
        }
        return $percent;
    }

    public function getProductTaxClassIds()
    {
        $productTaxIds = array();
        $productIds = Mage::getSingleton('checkout/cart')->getQuoteProductIds();
        if ($productIds) {
            $productModel = Mage::getSingleton('catalog/product');
            foreach ($productIds as $productId) {
                $productTaxIds[] = $productModel->load($productId)->getTaxClassId();
            }
            if ($productTaxIds) {
                $productTaxIds = array_unique($productTaxIds);
            }
        }
        return $productTaxIds;
    }

    public function getMultifeesPrice($fees, $subtotal, $shipping, $taxes = null)
    {
        $result = array();
        if (is_array($fees) && count($fees)) {
            $resPrice = 0;
            $resPriceInclTax = 0;
            $basePrice = 0;
            foreach ($fees as $feeId => $options) {
                foreach ($options['options'] as $optionId => $value) {
                    $price = 0;
                    $option = Mage::getSingleton('multifees/option')->load((int) $optionId);

                    if (Mage::helper('multifees')->isTypeFixed($option->getPriceType())) {
                        $price = $option->getPrice();
                    } else {
                        if ($subtotal > 0) {
                            if (!empty($options['price'][$optionId]))
                            {
                                $price = $options['price'][$optionId];
                            }
                            else
                            {
                                $price = ($subtotal * $option->getPrice()) / 100;
                            }
                        }
                    }
                    $priceInclTax = $this->getMultifeesTax($taxes, $price);
                    $basePrice += $price;
                    $resPriceInclTax += $this->convertPrice($priceInclTax);
                    $resPrice += $this->convertPrice($price);

                    $fees[$feeId]['price'][$optionId] = $this->convertPrice($price);
                    $fees[$feeId]['price_incl_tax'][$optionId] = $priceInclTax;
                    $fees[$feeId]['base_price'][$optionId] = $price;
                }
            }
            $result['base_fees'] = $basePrice;
            $result['fees_excl_tax'] = $resPrice;
            $result['base_fees_incl_tax'] = $priceInclTax;
            $result['fees'] = $resPriceInclTax;
            $result['details_fees'] = $fees;
        }
        return new Varien_Object($result);
    }

    public function setMultifeesTaxes($detailsFees, $taxes)
    {
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $subtotal = $totals["subtotal"]->getValue();

        foreach ($taxes as $i => $tax) {
            foreach ($detailsFees as $feeId => $options) {
                foreach ($options['options'] as $optionId => $value) {
                    $price = 0;
                    $option = Mage::getSingleton('multifees/option')->load((int) $optionId);
                    if (Mage::helper('multifees')->isTypeFixed($option->getPriceType())) {
                        $price = $option->getPrice();
                    } else {
                        if ($subtotal > 0) {
                            $price = ($subtotal * $option->getPrice()) / 100;
                        }
                    }
                    $tax['amount'] = $tax['amount'] + $this->convertPrice($this->getMultifeesTax($taxes, $price) - $price);
                    $tax['base_amount'] = $tax['base_amount'] + $this->getMultifeesTax($taxes, $price) - $price;
                }
            }
            $taxes[$i] = $tax;
        }
        return $taxes;
    }

    public function getMultifeesTax($taxes, $price, $onlyPercent = false)
    {
        if (is_array($taxes) && count($taxes) && $this->isIncludingTax()) {
            $amount = 0;
            foreach ($taxes as $row) {
                foreach ($row['rates'] as $tax) {
                    if ($tax['percent'] == 0) {
                        continue;
                    }
                    $percent = ($price * $tax['percent']) / 100;
                    $amount += $percent;
                }
            }
            if (true === $onlyPercent) {
                return $amount;
            } else {
                return $price + $amount;
            }
        } else {
            return $price;
        }
    }

    public function getFullTaxInfo($orderId)
    {
        $order = new Varien_Object(array('id' => $orderId));
        $rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order)->toArray();
        return Mage::getSingleton('tax/calculation')->reproduceProcess($rates['items']);
    }

    public function convertPrice($price)
    {
        return Mage::app()->getStore()->convertPrice($price);
    }

    public function getFeeTitle($feeId)
    {
        $resource = Mage::getResourceSingleton('multifees/language_fee');
        $result = $resource->getFee((int) $feeId, $this->getStoreId());
        if (!$result) {
            $result = $resource->getFee((int) $feeId, 0);
        }
        return $this->htmlEscape($result['title']);
    }

    public function getOptionTitle($optionId)
    {
        $optionModel = Mage::getResourceSingleton('multifees/language_option');
        $optionData = $optionModel->getOption($optionId);
        $storeOption = array();
        if ($optionData) {
            foreach ($optionData as $value) {
                $storeOption[$value['store_id']] = $this->htmlEscape($value['title']);
            }
        }
        $tmpOption = null;
        if (isset($storeOption[0])) {
            $tmpOption = $storeOption[0];
        }
        return isset($storeOption[$this->getStoreId()]) ? $storeOption[$this->getStoreId()] : $tmpOption;
    }

    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function getFilter($data)
    {
        $result = array();
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_StringTrim());
        $filter->addFilter(new Zend_Filter_StripTags());

        if ($data) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = $this->getFilter($value);
                } else {
                    $result[$key] = $filter->filter($value);
                }
            }
        }
        return $result;
    }

    public function setFieldOfForm(array $value, $empty = false)
    {
        if ($value) {
            $i = 0;
            if ($empty) {
                $result[$i] = array('label' => $this->__('-- Please Select --'), 'value' => '');
                $i++;
            }
            foreach ($value as $key => $val) {
                $result[$i]['label'] = $val;
                $result[$i]['value'] = $key;
                $i++;
            }
        }
        return $result;
    }

    public function isFeeEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_FEE_ENABLED);
    }

    public function isStoreMultifees()
    {
        return count(Mage::getSingleton('checkout/session')->getStoreMultifees());
    }

    public function setDetailsMultifees()
    {
        return serialize(Mage::getSingleton('checkout/session')->getDetailsMultifees());
    }

    public function isNoEmptyFees($detailsFees)
    {
        if (!is_array($detailsFees)) {
            $detailsFees = @unserialize($detailsFees);
        }
        return (count($detailsFees) > 0);
    }

    public function getDetailsMultifeesView($detailsFees, $isAdminhtml = false)
    {
        if (!is_array($detailsFees)) {
            $detailsFees = @unserialize($detailsFees);
        }
        if ($isAdminhtml === true) {
            $block = Mage::app()->getLayout()
                            ->createBlock('mageworx/multifees_adminhtml_sales_order_fee')
                            ->addData(array('items' => $detailsFees))
                            ->toHtml();
        } else {
            $block = Mage::app()->getLayout()
                            ->createBlock('core/template')
                            ->setTemplate('multifees/details-fee.phtml')
                            ->addData(array('items' => $detailsFees))
                            ->toHtml();
        }
        return $block;
    }

    public function getOptionImgView($option)
    {
        $block = Mage::app()->getLayout()
                        ->createBlock('core/template')
                        ->setTemplate('multifees/option_image.phtml')
                        ->addData(array('items' => $option))
                        ->toHtml();

        return $block;
    }

    public function getFileName($filePath)
    {
        $name = '';
        $name = substr(strrchr($filePath, '/'), 1);
        if (!$name) {
            $name = substr(strrchr($filePath, '\\'), 1);
        }
        return $name;
    }

    public function getFiles($path)
    {
        return @glob($path . "*.*");
    }

    public function isMultifeesFile($optionId)
    {
        return $this->getFiles($this->getMultifeesPath($optionId));
    }

    public function getMultifeesPath($feeOptionId)
    {
        return Mage::getBaseDir('media') . DS . 'multifees' . DS . $feeOptionId . DS;
    }

    public function getImageView($optionId, $isRealSize = false)
    {
        $files = $this->getFiles($this->getMultifeesPath($optionId));
        if ($files) {
            $image = new Varien_Image($files[0]);

            $origHeight = $image->getOriginalHeight();
            $origWidth = $image->getOriginalWidth();
            $width = null;
            $height = null;
            if (Mage::app()->getStore()->isAdmin()) {
                if ($origHeight > $origWidth) {
                    $height = self::DEFAULT_IMG_SIZE;
                } else {
                    $width = self::DEFAULT_IMG_SIZE;
                }
            } else {
                $configWidth = (int) Mage::getStoreConfig(self::XML_IMG_MAX_WIDTH);
                $configHeight = (int) Mage::getStoreConfig(self::XML_IMG_MAX_HEIGHT);
                if (empty($configWidth)) {
                    $configWidth = self::DEFAULT_IMG_SIZE;
                }
                if (empty($configHeight)) {
                    $configHeight = self::DEFAULT_IMG_SIZE;
                }
                if ($origHeight > $origWidth) {
                    $height = $configHeight;
                } else {
                    $width = $configWidth;
                }
            }
            if ($isRealSize === false) {
                $image->resize($width, $height);
            }
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(false);
            $image->display();
        }
    }

    public function getOptionImgHtml(MageWorx_MultiFees_Model_Option $option, $groupId = null)
    {
        if (is_null($groupId)) {
            $groupId = uniqid();
        }
        $file = $this->isMultifeesFile($option->getId());
        if ($file) {
            $fileName = $this->getFileName($file[0]);
            $impOption = array(
                'big_img_url' => Mage::getModel('core/url')->getUrl('multifees/cart/getImage', array('option' => $option->getId(), 'big-image' => true, 'file' => $fileName)),
                'url' => Mage::getModel('core/url')->getUrl('multifees/cart/getImage', array('option' => $option->getId(), 'file' => $fileName)),
                'option' => $option,
                'group_id' => $groupId,
            );
            return $this->getOptionImgView(new Varien_Object($impOption));
        }
    }

    public function codeOptionKey($optionId)
    {
        return md5($optionId);
    }

    public function getPaymentMethods($storeId = null)
    {
        $result = array();
        $methods = Mage::getSingleton('payment/config')->getAllMethods();
        if ($methods) {
            foreach ($methods as $code => $item) {
                $result[] = array(
                    'label' => Mage::getStoreConfig('payment/' . $code . '/title', $storeId),
                    'value' => $item->getId()
                );
            }
        }
        return $result;
    }

    public function getShippingMethods($storeId = null)
    {
        $result = array();
        $methods = Mage::getSingleton('shipping/config')->getAllCarriers();
        if ($methods) {
            foreach ($methods as $code => $item) {
                if ('googlecheckout' != $code) {
                    $result[] = array(
                        'label' => Mage::getStoreConfig('carriers/' . $code . '/title', $storeId),
                        'value' => $item->getId()
                    );
                }
            }
        }
        return $result;
    }

}