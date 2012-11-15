<?php

class MDN_QuickProductCreation_Block_System_Config_Form_Fieldset_Websites extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_values;

    /**
     * Fieldset renderere
     * @param Varien_Data_Form_Element_Abstract $element
     * @return <type>
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $html = $this->_getHeaderHtml($element);

        $websites = mage::getModel('Adminhtml/System_Config_Source_Website')->toOptionArray();

        foreach ($websites as $website)
        {
            $fieldHtml = $this->_getFieldHtml($element, $website);
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
    protected function _getFieldHtml($fieldset, $website)
    {

        $path = 'groups[websites][fields]['.$website['value'].'][value]';
        $value = $this->getItemValue($website['value']);

        $element = $fieldset->addField($website['value'], 'select',
            array(
                'name'      => $path,
                'label'     => $website['label'],
                'value' => $value,
                'values' => $this->getOptions()
            )
        );

        return $element->toHtml();
    }

    public function getOptions()
    {
        $options = array(
                            array('label'=>Mage::helper('adminhtml')->__('Disable'), 'value'=>0),
                            array('label'=>Mage::helper('adminhtml')->__('Enable'), 'value'=>1)
                        );


        return $options;
    }

    protected function getItemValue($websiteCode)
    {
        $path = 'quickproductcreation/websites/'.$websiteCode;
        $configData = $this->getConfigData();
        if (isset($configData[$path]))
            return $configData[$path];
        else
            return null;
    }
}
