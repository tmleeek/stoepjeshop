<?php

class MDN_QuickProductCreation_Block_Admin_Form extends Mage_Core_Block_Template
{
    private $_attributes = null;

    /**
     * Return editable attributes
     * @return <type>
     */
    public function getAttributes()
    {
        if ($this->_attributes == null)
        {
            $this->_attributes = mage::helper('QuickProductCreation/ProductAttributes')->getEditableAttributes();
        }
        return $this->_attributes;
    }

    /**
     * Return table row to edit one attribute value
     * @param <type> $att
     * @return <type>
     */
    public function getAttributeControl($att)
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset');

        $name = 'products[{id}]['.$att->getattribute_code().']';

        return mage::helper('QuickProductCreation/ProductAttributes')->getAttributeControl($fieldset, $att, null, $name, true);
    }

    public function getAttributeSetCombo($name)
    {
        $html = '<select name="'.$name.'" id="'.$name.'">';

        $collection = mage::helper('QuickProductCreation/ProductAttributes')->getAttributeSets();
        $defaultValue = mage::getStoreConfig('quickproductcreation/miscellaneous/default_attribute_set');
        
        foreach($collection as $attributeSet)
        {
            $selected = '';
            if ($attributeSet->getId() == $defaultValue)
                    $selected = ' selected ';
            $html .= '<option value="'.$attributeSet->getId().'" '.$selected.'>'.$attributeSet->getattribute_set_name().'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getProductTypeCombo($name)
    {
        $html = '<select name="'.$name.'" id="'.$name.'">';

        $types = array('simple', 'downloadable', 'virtual');

        foreach($types as $type)
        {
            $html .= '<option value="'.$type.'">'.$this->__($type).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function cleanUpForJS($html)
    {
        //remove line returns
        $html = str_replace("\n", "", $html);

        //remove label
        $t = explode("</label>", $html);
        $html = $t[1];
        $html = substr($html, 0, strlen($html) - strlen("</span>"));

        return $html;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('QuickProductCreation/Admin/CreateProducts');
    }

    public function getStockInput($name)
    {
        $html = '<input type="text" size="4" name="'.$name.'" id="'.$name.'" value="0">';
        return $html;
    }


    public function getNotifyQtyCombo($name)
    {
        $defaultValue = mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');

        $html = '<input type="text" size="4" name="'.$name.'" id="'.$name.'" value="'.$defaultValue.'">';
        return $html;
    }

    public function getBackOrdersCombo($name)
    {
        $html = '<select name="'.$name.'" id="'.$name.'">';
        $defaultValue = mage::getStoreConfig('cataloginventory/item_options/backorders');

        $items = mage::getModel('cataloginventory/source_backorders')->toOptionArray();

        foreach($items as $item)
        {
            $selected = '';
            if ($item['value'] == $defaultValue)
                $selected = ' selected ';
            $html .= '<option '.$selected.' value="'.$item['value'].'">'.$item['label'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }


}