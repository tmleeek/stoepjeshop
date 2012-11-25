<?php

    require_once(BP . DS . 'app' . DS . 'etc' . DS . 'magictoolbox' . DS . 'core' . DS . 'magiczoom.module.core.class.php');
    $tool = new MagicZoomModuleCoreClass();

    // allow to use different ini files for different themes
    // get ini file from current theme folder by default
    $interface = Mage::getSingleton('core/design_package')->getPackageName();
    $theme = Mage::getSingleton('core/design_package')->getTheme('template');
    $iniFile = BP . DS . 'app' . DS . 'design' . DS . 'frontend' . DS . $interface . DS . $theme . DS . 'magiczoom.settings.ini';
    if(!file_exists($iniFile)) {
        // if we can't found ini file for current theme we should get default ini file
        $iniFile = BP . DS . 'app' . DS . 'etc' . DS . 'magictoolbox' . DS . 'magiczoom.settings.ini';
    }
    // load INI
    $tool->params->loadINI($iniFile);

    /* load locale */
    $mz_lt = $this->__('MagicZoom_LoadingText');
    if($mz_lt != 'MagicZoom_LoadingText') {
        $tool->params->set('loading-msg', $mz_lt);
    }

    $mz_m = $this->__('MagicZoom_Message');
    if($mz_m != 'MagicZoom_Message') {
        $tool->params->set('message', $mz_m);
    }



    $GLOBALS["magictoolbox"]["magiczoom"] = & $tool;

    require_once(BP . DS . 'app' . DS . 'etc' . DS . 'magictoolbox' . DS . 'core' . DS . 'addons.php');

?>
