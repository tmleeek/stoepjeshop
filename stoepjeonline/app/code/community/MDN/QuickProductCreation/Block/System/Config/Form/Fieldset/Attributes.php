<?php

class MDN_QuickProductCreation_Block_System_Config_Form_Fieldset_Attributes extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_values;

    const XML_PATH_EXCLUDED_ATTRIBUTES = 'quickproductcreation/excluded_attributes';

    /**
     * Fieldset renderere
     * @param Varien_Data_Form_Element_Abstract $element
     * @return <type>
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $html = $this->_getHeaderHtml($element);

        $attributes = mage::helper('QuickProductCreation/ProductAttributes')->getAttributes();

        foreach ($attributes as $attribute)
        {
            $attCode = $attribute->getAttributeCode();
            if ($this->exclude($attCode))
                continue;

            $inputType = $attribute->getFrontend()->getInputType();
            if (!$inputType)
                continue;

            if (!$attribute->getis_visible())
                continue;

            //add editable checkbox
            $checkboxHtml = '<td class="value" style="width: 100px;">'.$this->getYesNoComboBox($attCode, 'edit').'</td>';
            $checkboxHtml .= '<td class="value" style="width: 100px;">'.$this->getYesNoComboBox($attCode, 'apply_default').'</td><td class="value">';

            //add default value input control
            $fieldHtml = $this->_getFieldHtml($element, $attribute);
            $fieldHtml = str_replace("<td class=\"value\">", $checkboxHtml, $fieldHtml);

            $html .= $fieldHtml;
        }
        
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Return renderer
     * @return <type>
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    /**
     * Render one attribute
     * @param <type> $fieldset
     * @param <type> $attribute
     * @return <type>
     */
    protected function _getFieldHtml($fieldset, $attribute)
    {
        $attributeCode = $attribute['attribute_code'];

        $path = 'groups[attributes][fields]['.$attributeCode.'_value][value]';
        $value = $this->getItemValue($attributeCode, 'value');

        $configData = $this->getConfigData();
        $data = isset($configData[$path]) ? $configData[$path] : array();

        $inputType = $attribute->getFrontend()->getInputType();
        $originalInputType = $inputType;
        $inputType = mage::helper('QuickProductCreation/ProductAttributes')->forceInputType($inputType);

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $element = $fieldset->addField($attribute->getAttributeCode(), $inputType,
            array(
                'name'      => $path,
                'label'     => $attribute->getFrontend()->getLabel(),
                //'class'     => $attribute->getFrontend()->getClass(),
                'format'    => $dateFormatIso,
                'value' => $value
            )
        )
        ->setEntityAttribute($attribute)
        ;

        //Define source for select input type
        if ($inputType == 'select' || $inputType == 'multiselect') {
            if ($originalInputType == 'boolean')
            {
                $options = array();
                $options[] = array('key' => '', 'label' => '');
                $options[] = array('key' => '0', 'label' => $this->__('No'));
                $options[] = array('key' => '1', 'label' => $this->__('Yes'));
                $element->setValues($options);
            }
            else
            {
                if ($attribute->getSourceModel())
                    $element->setValues($attribute->getFrontend()->getSelectOptions());
                else
                {
                    $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setPositionOrder('desc', true)
                        ->load();

                    $options = array();
                    $options[] = array('key' => '', 'label' => '');
                    foreach($optionCollection as $elt)
                    {
                        $options[] = array('value' => $elt['option_id'], 'label' => $elt['value']);
                    }
                    $element->setValues($options);
                }
            }
        }

        return $element->toHtml();
    }

    /**
     * Set if attribute MUST NOT be displayed
     * @param <type> $attributeCode
     * @return <type>
     */
    protected function exclude($attributeCode)
    {
        $excludedAttributes = Mage::getConfig()
            ->getNode(self::XML_PATH_EXCLUDED_ATTRIBUTES)
            ->asArray();

        return array_key_exists($attributeCode, $excludedAttributes);
        
    }

    protected function getItemValue($attributeCode, $mode)
    {
        $path = 'quickproductcreation/attributes/'.$attributeCode.'_'.$mode;

        if ($mode == 'edit')
        {
            $required = mage::helper('QuickProductCreation')->attributeIsRequiredForEdit($attributeCode);
            if ($required)
                return 1;
        }

        $configData = $this->getConfigData();
        if (isset($configData[$path]))
            return $configData[$path];
        else
            return null;
    }

    protected function getYesNoComboBox($attributeCode, $mode)
    {
        $name = 'groups[attributes][fields]['.$attributeCode.'_'.$mode.'][value]';
        $value = $this->getItemValue($attributeCode, $mode);

        $html = '<select name="'.$name.'" id="'.$name.'" style="width: 100px;">';

        $selected = '';
        if ($value != 1)
            $selected = ' selected ';
        $html .= '<option value="0" '.$selected.'>'.$this->__('No').'</option>';

        $selected = '';
        if ($value == 1)
            $selected = ' selected ';
        $html .= '<option value="1" '.$selected.'>'.$this->__('Yes').'</option>';

        $html .= '</select>';

        return $html;
    }

    /**
     * Return header html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<div  class="entry-edit-head collapseable" ><a id="'.$element->getHtmlId().'-head" href="#" onclick="Fieldset.toggleCollapse(\''.$element->getHtmlId().'\', \''.$this->getUrl('*/*/state').'\'); return false;">'.$element->getLegend().'</a></div>';
        $html.= '<input id="'.$element->getHtmlId().'-state" name="config_state['.$element->getId().']" type="hidden" value="'.(int)$this->_getCollapseState($element).'" />';
        $html.= '<fieldset class="'.$this->_getFieldsetCss().'" id="'.$element->getHtmlId().'">';
        $html.= '<legend>'.$element->getLegend().'</legend>';

        if ($element->getComment()) {
            $html .= '<div class="comment">'.$element->getComment().'</div>';
        }
        // field label column
        $html.= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html.= '<colgroup class="use-default" />';
        }
        $html.= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        $html .= '<thead><tr>';
        $html .= '<th></th>';
        $html .= '<th class="a-center">'.$this->__('Editable').'</th>';
        $html .= '<th class="a-center">'.$this->__('Apply default value').'</th>';
        $html .= '<th class="a-center">'.$this->__('Default value').'</th>';
        $html .= '</tr></thead>';

        return $html;
    }


}
