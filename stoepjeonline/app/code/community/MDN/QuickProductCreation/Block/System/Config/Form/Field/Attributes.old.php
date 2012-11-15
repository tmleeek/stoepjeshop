<?php

class MDN_QuickProductCreation_Block_System_Config_Form_Field_Attributes extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Render control
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<table border="1" cellspacing="0">';
        $html .= '<tr>';
        $html .= '<th>'.$this->__('Attribute').'</th>';
        $html .= '<th>'.$this->__('Editable').'</th>';
        $html .= '<th>'.$this->__('Default value').'</th>';
        $html .= '</tr>';
        $attributes = mage::helper('QuickProductCreation/ProductAttributes')->getAttributes();
        foreach($attributes as $att)
        {
            if (!$att['frontend_input'])
                continue;

            $attributeCode = $att['attribute_code'];

            $html .= '<tr>';
            $html .= '<td>'.$attributeCode.' ->'.$att['frontend_input'].'</td>';
            $html .= '<td>'.$this->getEditableControl($att).'</td>';
            $html .= '<td>'.$this->getDefaultValueControl($att).'</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }


   /**
    * Return control to set if attributes is editable in creation form
    * @param <type> $attCode
    * @return string
    */
   protected function getEditableControl($att)
   {
        $attCode = $att['attribute_code'];

        $name = 'attributes['.$attCode.'][editable]';
        $options = array('0' => $this->__('No'), '1' => $this->__('Yes'));

        $html = '<select name="'.$name.'" id="'.$name.'" style="width: 80px;">';

        foreach($options as $key => $label)
        {
            $html .= '<option value="'.$key.'">'.$label.'</option>';
        }

        $html .= '</select>';
        return $html;
   }

   /**
    * Return control to set default attribute value
    * @param <type> $attCode
    * @return string
    */
   protected function getDefaultValueControl($att)
   {
       $attCode = $att['attribute_code'];

       $name = 'attributes['.$attCode.'][default]';

       switch($att['frontend_input'])
       {
           case 'text':
           case 'textarea':
               $html = '<input type="text" name="'.$name.'" id="'.$name.'" size="50" value="" />';
               break;
           case 'select':
               if (!$att['source_model'])
                   return 'XXX';
                $sourceModel = mage::getSingleton($att['source_model']);
                if (!is_object($sourceModel))
                    return 'XX';

                $html = '<select name="'.$name.'" id="'.$name.'" />';
                foreach($sourceModel->getAllOptions() as $option)
                {
                    $html .= '<option value="'.$option['value'].'">'.$option['label'].'</<option>';
                }
                $html .= '</select>';
                
                break;
           default:
               $html = $att['frontend_input'];
               break;
       }

       return $html;
   }
}
