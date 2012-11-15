<?php
class Devinc_Dailydeal_Model_Sidebar extends Varien_Object 
{
    public function toOptionArray()
    {	
        $sidebar = array();
            
		$sidebar[] = 'No';
		$sidebar[] = 'Left Sidebar';
		$sidebar[] = 'Right Sidebar';
		$sidebar[] = 'Both';
		
        return $sidebar;
    }

}
?>