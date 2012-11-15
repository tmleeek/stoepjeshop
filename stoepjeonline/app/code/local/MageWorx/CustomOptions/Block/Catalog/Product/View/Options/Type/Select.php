<?php

class MageWorx_CustomOptions_Block_Catalog_Product_View_Options_Type_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select {

    static $isFirstOption = true;

    public function getValuesHtml() {
        $_option = $this->getOption();
        $displayQty = Mage::helper('customoptions')->canDisplayQtyForOptions();
        $hideOutOfStockOptions = Mage::helper('customoptions')->canHideOutOfStockOptions();
        $enabledInventory = Mage::helper('customoptions')->isInventoryEnabled();
        
        if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
            $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());                                                    
        } else {
            $configValue= false;
        }
        
        

        if ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN || $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required-entry' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock('core/html_select')
                    ->setData(array(
                        'id' => 'select_' . $_option->getId(),
                        'class' => $require . ' product-custom-option'
                    ));
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $_option->getid() . ']')
                        ->addOption('', $this->__('-- Please Select --'));
            } else {
                $select->setName('options[' . $_option->getid() . '][]');
                $select->setClass('multiselect' . $require . ' product-custom-option');
            }
            $imagesHtml = '';
            $defaultFlag = false;
            
            foreach ($_option->getValues() as $_value) {
                $qty = '';

                if ($enabledInventory && $hideOutOfStockOptions && $_value->getCustomoptionsQty() === '0') {
                    continue;
                }

                $selectOptions = array();
                if ($enabledInventory && $_value->getCustomoptionsQty() === '0') {
                    $selectOptions['disabled'] = 'disabled';
                }
                if ($_value->getDefault() == 1 && !isset($selectOptions['disabled']) && !$configValue) {
                    $selectOptions['selected'] = 'selected';
                    $defaultFlag = true;
                }

                if ($enabledInventory && $displayQty && $_value->getCustomoptionsQty() !== '') {
                    $qty = ' (' . ($_value->getCustomoptionsQty() > 0 ? $_value->getCustomoptionsQty() : 'Out of stock') . ')';
                }

                $path = $_value->getImagePath();
                $path = explode('/', $path);
                if (count($path) > 2) {
                    if (file_exists(Mage::getBaseDir('media') . DS . 'customoptions' . DS . $_value->getImagePath())) {
                        $html = Mage::helper('customoptions')->getValueImgHtml($path[0], $path[1], $path[2], true, $_value->getOptionTypeId(), $_option->getId());
                        $imagesHtml .= $html;
                    }
                }

                $priceStr = $this->_formatPrice(array(
                            'is_percent' => ($_value->getPriceType() == 'percent') ? true : false,
                            'pricing_value' => $_value->getPrice(true)
                                ), false);
                $select->addOption($_value->getOptionTypeId(), $_value->getTitle() . ' ' . $priceStr . $qty, $selectOptions);
            }
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            
            $select->setExtraParams('onchange="opConfig.reloadPrice(); showCustomOptionsValueImage(' . $_option->getId() . ')"' . $extraParams);            
            
            if ($configValue) {
                $select->setValue($configValue);
            }
            if ((count($select->getOptions())>1 && $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) || (count($select->getOptions())>0 && $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE)) {
                if (Mage::helper('customoptions')->isImagesAboveOptions()) $outHTML = $imagesHtml.$select->getHtml(); else $outHTML = $select->getHtml().$imagesHtml;                
                if ($defaultFlag) $outHTML.='<script type="text/javascript">showCustomOptionsValueImage('.$_option->getId().');</script>'; 
                return $outHTML;
            }    
        
            
        } elseif ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO || $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX) {
            $selectHtml = '';
            $require = ($_option->getIsRequire()) ? ' validate-one-required-by-name' : '';
            $arraySign = '';
            switch ($_option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<li><input type="radio" id="options_' . $_option->getId() . '" class="' . $class . ' product-custom-option" name="options[' . $_option->getId() . ']" onclick="opConfig.reloadPrice()" value="" checked="checked" /><span class="label"><label for="options_' . $_option->getId() . '">' . $this->__('None') . '</label></span></li>';
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;
                $priceStr = $this->_formatPrice(array(
                            'is_percent' => ($_value->getPriceType() == 'percent') ? true : false,
                            'pricing_value' => $_value->getPrice(true)
                        ));
                $qty = '';
                if ($enabledInventory && $hideOutOfStockOptions && $_value->getCustomoptionsQty() === '0') {
                    continue;
                }
                $disabled = $enabledInventory && $_value->getCustomoptionsQty() === '0' ? 'disabled="disabled"' : '';

                if ($enabledInventory && $displayQty && $_value->getCustomoptionsQty() !== '') {
                    $qty = ' (' . ($_value->getCustomoptionsQty() > 0 ? $_value->getCustomoptionsQty() : 'Out of stock') . ')';
                }
                $selectHtml .= '<li>';
                $path = $_value->getImagePath();
                $path = explode('/', $path);
                if (count($path) > 2) {
                    if (file_exists(Mage::getBaseDir('media') . DS . 'customoptions' . DS . $_value->getImagePath())) {
                        $html = Mage::helper('customoptions')->getValueImgHtml($path[0], $path[1], $path[2]);
                        $selectHtml .= $html;
                    }
                }
                                                
                if ($configValue) {
                    $htmlValue = $_value->getOptionTypeId();
                    if ($arraySign) {
                        $checked = (is_array($configValue) && in_array($htmlValue, $configValue)) ? 'checked' : '';
                    } else {
                        $checked = $configValue == $htmlValue ? 'checked' : '';
                    }
                } else {
                    $checked = ($_value->getDefault()==1 && !$disabled) ? 'checked' : '';
                }    
                

                
                if (Mage::helper('customoptions')->isQntyInputEnabled() && $_option->getQntyInput() && $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX) {
                    $selectHtml .=
                        '<input ' . $disabled . ' ' . $checked . ' type="' . $type . '" class="' . $class . ' ' . $require . ' product-custom-option" onclick="$(\'options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty\').disabled=!this.checked; if ($(\'options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty\').value<=0) $(\'options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty\').value=1;  opConfig.reloadPrice();" name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId() . '_' . $count . '" value="' . $_value->getOptionTypeId() . '" />' .
                        '<span class="label">
                            <label for="options_' . $_option->getId() . '_' . $count . '">' . $_value->getTitle() . ' ' . $priceStr . $qty . '</label>
                            &nbsp;&nbsp;&nbsp;
                            <label><b>'.Mage::helper('sales')->__('Qty').':</b> <input type="text" class="input-text qty validate-greater-than-zero" title="'.Mage::helper('sales')->__('Qty').'" value="1" maxlength="12" id="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" name="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" onchange="opConfig.reloadPrice()" '.($_value->getDefault()==1?$disabled:'disabled').'></label>
                         </span>';
                } else {
                    $selectHtml .=
                        '<input ' . $disabled . ' ' . $checked . ' type="' . $type . '" class="' . $class . ' ' . $require . ' product-custom-option" onclick="opConfig.reloadPrice();" name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId() . '_' . $count . '" value="' . $_value->getOptionTypeId() . '" />' .
                        '<span class="label"><label for="options_' . $_option->getId() . '_' . $count . '">' . $_value->getTitle() . ' ' . $priceStr . $qty . '</label></span>';
                }
                                                
                if ($_option->getIsRequire()) {
                    $selectHtml .= '<script type="text/javascript">' .
                            '$(\'options_' . $_option->getId() . '_' . $count . '\').advaiceContainer = \'options-' . $_option->getId() . '-container\';' .
                            '$(\'options_' . $_option->getId() . '_' . $count . '\').callbackFunction = \'validateOptionsCallback\';' .
                            '</script>';
                }
                $selectHtml .= '</li>';
            }                       
            
            if ($selectHtml) $selectHtml = '<ul id="options-' . $_option->getId() . '-list" class="options-list">'.$selectHtml.'</ul>';
            self::$isFirstOption = false;
            return $selectHtml;
        }
    }

}
