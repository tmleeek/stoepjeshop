<?php

    require_once(BP . str_replace('/', DS, '/app/etc/magictoolbox/core/load.php'));

    $tool = & $GLOBALS["magictoolbox"]["magiczoom"];

    function magictoolboxCheckPage($page) {
        $cls = array(
            'product' => 'Mage_Catalog_Block_Product_View_Media',
            'category' => 'Mage_Catalog_CategoryController',
            'search' => 'Mage_CatalogSearch_ResultController',
            'catalognew' => 'Thirty4_CatalogNew_IndexController'
        );
        $decls = get_declared_classes();
        $declared = in_array($cls[$page], $decls) || in_array(strtolower($cls[$page]), $decls);
        $tool = & $GLOBALS["magictoolbox"]["magiczoom"];
        if($page == 'search' || $page == 'catalognew') {
            $page = 'category';
        }
        if($declared && !$tool->params->checkValue('use-effect-on-' . $page . '-page', 'No')) {
            return true;
        } else {
            return false;
        }
    }

    if(magictoolboxCheckPage('product') || magictoolboxCheckPage('category') || magictoolboxCheckPage('search') || magictoolboxCheckPage('catalognew')) {
        echo $tool->headers($this->getSkinUrl('js'), $this->getSkinUrl('css'));
        echo '<script type="text/javascript" src="' . $this->getSkinUrl('js') . '/magictoolbox_utils.js"></script>';

        if(magictoolboxCheckPage('product')) {
            $f = 'function(){MagicToolbox_findOption(\'' . strtolower(preg_replace('/\s*,\s*/is', ',', $tool->params->getValue('option-associated-with-images'))) . '\');}';
            echo '<script type="text/javascript">
                    var MagicToolbox_click = \'' . strtolower($tool->params->getValue('thumb-change')) . '\';




                    $j(window).a(\'load\', ' . $f . ');

                </script>';
        }
        echo '<style type="text/css">div.MagicToolboxContainer {text-align: center;}' .

            '</style>';

    }

?>
