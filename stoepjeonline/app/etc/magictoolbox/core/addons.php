<?php

    if(!function_exists('magicToolboxResizer')) {
        function magicToolboxResizer($product = null, $watermark = 'image', $s = null, $imageFile = null) {
            if($product == null) return false;

            $subdir = 'image';
            $helper = Mage::helper('catalog/image')->init($product, $subdir, $imageFile);
            if($s !== null) {
                $helper->watermark(Mage::getStoreConfig('design/watermark/' . $watermark . '_image'),
                    Mage::getStoreConfig('design/watermark/' . $watermark . '_position'),
                    Mage::getStoreConfig('design/watermark/' . $watermark . '_size'),
                    Mage::getStoreConfig('design/watermark/' . $watermark . '_imageOpacity'));
            }

            $model = Mage::getModel('catalog/product_image');
            $model->setDesctinationSubdir($subdir);
            try {
                if($imageFile == null) {
                    $model->setBaseFile($product->getData($subdir));
                } else {
                    $model->setBaseFile($imageFile);
                }
            } catch ( Exception $e ) {
                $img = Mage::getDesign()->getSkinUrl() . $helper->getPlaceholder();
                if($s == null) return $img;
                return array($img, $img);
            }

            $img = $helper->__toString();
            if($s == null) return $img;

            $squareImages = false;
            $tool = &$GLOBALS["magictoolbox"]["magiczoom"];
            if($tool) {
                if($tool->params->checkValue('square-images', 'Yes')) {
                    $squareImages = true;
                }
            }

            if(!$squareImages) {
                $size = getimagesize($model->getBaseFile());
                $w = $s;
                $h = round($s * $size[1] / $size[0]);
                if($h > $s && $tool->params->checkValue('size-depends', 'both') || $tool->params->checkValue('size-depends', 'height')) {
                    $h = $s;
                    $w = round($s * $size[0] / $size[1]);
                }
            } else {
                $h = $w = $s;
            }

            $helper->resize($w, $h);
            $thumb = $helper->__toString();
            return array($img, $thumb);
        }
    }

?>
