<?php

class MDN_QuickProductCreation_Model_System_Config_Backend_Attributes extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave()
    {
        $value = $this->getValue();

        die('toto');

        return $this;
    }

}
