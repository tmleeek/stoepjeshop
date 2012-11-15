<?php

class MDN_QuickProductCreation_Helper_ProductAttributes extends Mage_Core_Helper_Abstract
{
   /**
    * Return product attributes
    * @return <type>
    */
   public function getAttributes()
   {
       $entityCode = 'catalog_product';
       $entityType = Mage::getModel('eav/entity_type')->loadByCode($entityCode);

       $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($entityType)
                        ->addStoreLabel(0);

        return $collection;
   }

   /**
    * return attributes that are editable (set in system > config > quick product creation)
    */
   public function getEditableAttributes()
   {
        $editableAttributes = array();

        foreach($this->getAttributes() as $att)
        {
            $attCode = $att->getAttributeCode();
            if ($this->attributeIsEditable($attCode))
            {
                $editableAttributes[] = $att;
            }
        }

        return $editableAttributes;
   }

   public function getApplyDefaultValueAttributes()
   {
        $applyDefaultValueAttributes = array();

        foreach($this->getAttributes() as $att)
        {
            $attCode = $att->getAttributeCode();
            if ($this->attributeApplyDefaultValue($attCode))
            {
                $applyDefaultValueAttributes[] = $att;
            }
        }

        return $applyDefaultValueAttributes;
   }

   /**
    * Return html control for attribute
    *
    * @param <type> $fieldset
    * @param <type> $attribute
    * @param <type> $value
    * @param <type> $name
    * @param <type> $addCssClass
    * @return <type>
    */
   public function getAttributeControl($fieldset, $attribute, $value, $name, $addCssClass = false)
   {
        $attCode = $attribute->getAttributeCode();
        $additionalClass = $this->getAdditionalClass($attribute);
        $inputType = $attribute->getFrontend()->getInputType();
        $originalInputType = $inputType;
        $inputType = $this->forceInputType($inputType);

        //affect default value if no parameter given
        if ($value == null)
            $value = $this->getAttributeDefaultValue($attCode);

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $element = $fieldset->addField($name, $inputType,
            array(
                'name'      => $name,
                'label'     => $attribute->getFrontend()->getLabel(),
                'class'     => ($addCssClass ? $attribute->getFrontend()->getClass() : '').' '.$additionalClass,
                'format'    => $dateFormatIso,
                'value' => $value
            )
        )
        ->setEntityAttribute($attribute);

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
    * Return true if attribute is set as editable
    * @param <type> $attributeCode
    * @return <type>
    */
   protected function attributeIsEditable($attributeCode)
   {
        $required = mage::helper('QuickProductCreation')->attributeIsRequiredForEdit($attributeCode);
        if ($required)
            return true;

        $path = 'quickproductcreation/attributes/'.$attributeCode.'_edit';
        return (mage::getStoreConfig($path) == 1);
   }

   /**
    * Return true if we have to apply default value to attribute (set in system > configuration > quickproductcreation)
    * @param <type> $attributeCode
    * @return <type>
    */
   protected function attributeApplyDefaultValue($attributeCode)
   {
       $path = 'quickproductcreation/attributes/'.$attributeCode.'_apply_default';
       return (mage::getStoreConfig($path) == 1);
   }

   /**
    * Return default value for attribute  (set in system > configuration > quickproductcreation)
    * @param <type> $attributeCode
    * @return <type>
    */
   public function getAttributeDefaultValue($attributeCode)
   {
       if (!$this->attributeApplyDefaultValue($attributeCode))
           return null;

       $path = 'quickproductcreation/attributes/'.$attributeCode.'_value';

       return mage::getStoreConfig($path);
   }
   /**
    * Force attribute input type
    * 
    * @param string $inputType
    * @return string
    */
    public function forceInputType($inputType)
    {
        switch($inputType)
        {
            case 'price':
                $inputType = 'text';
                break;
            case 'boolean':
                $inputType = 'select';
                break;
        }
        return $inputType;
    }

    protected function getAdditionalClass($attribute)
    {
        $class = '';
        switch($attribute->getFrontend()->getInputType())
        {
            case 'price':
                $class = 'validate-not-negative-number';
                break;
        }

        return $class;
    }

    /**
     * Return attributesets collection for products
     * @return <type>
     */
    public function getAttributeSets()
    {
        $entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityType->getId());
        return $collection;
    }
}