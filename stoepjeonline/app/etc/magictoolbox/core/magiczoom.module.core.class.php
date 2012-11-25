<?php

if(!in_array('MagicZoomModuleCoreClass', get_declared_classes())) {

    require_once(dirname(__FILE__) . '/magictoolbox.params.class.php');
	require_once 'app/Mage.php';

    class MagicZoomModuleCoreClass extends Mage_Core_Block_Template {
        var $uri;
        var $jsPath;
        var $cssPath;
        var $imgPath;
        var $params;
        var $general;//initial parameters
        var $type = 'standard';

        function MagicZoomModuleCoreClass() {
            $this->params = new MagicToolboxParams();
            $this->general = new MagicToolboxParams();
            $this->_paramDefaults();
        }

        function getValue($name) {
            switch($name) {
                case 'name': return 'MagicZoom'; break;
                case 'description': return 'MagicZoom description'; break;
                case 'id': return 'magiczoom'; break;
            }
        }

        function headers($jsPath = '', $cssPath = null, $notCheck = false) {
            if($cssPath == null) $cssPath = $jsPath;

            $config = array();

            $config[] = "opacity: " . $this->params->getValue('opacity');
            $config[] = "'zoom-width': " . $this->params->getValue('zoom-width');
            $config[] = "'zoom-height': " . $this->params->getValue('zoom-height');
            $config[] = "'zoom-position': '" . $this->params->getValue('zoom-position') . "'";
            $config[] = "'thumb-change': '" . $this->params->getValue('thumb-change') . "'";
            $config[] = "'smoothing-speed': " . $this->params->getValue('smoothing-speed');
            $config[] = "'zoom-distance': " . $this->params->getValue('zoom-distance');
            $config[] = "'selectors-mouseover-delay': " . $this->params->getValue('selectors-mouseover-delay');
            $config[] = "'zoom-fade-in-speed': " . $this->params->getValue('zoom-fade-in-speed');
            $config[] = "'zoom-fade-out-speed': " . $this->params->getValue('zoom-fade-out-speed');
            //$config[] = "hotspots: " . $this->params->getValue('hotspots');
            $config[] = "fps: " . $this->params->getValue('fps');
            $config[] = "'loading-msg': '" . $this->params->getValue('loading-msg') . "'";
            $config[] = "'loading-opacity': " . $this->params->getValue('loading-opacity');
            $config[] = "'loading-position-x': " . $this->params->getValue('loading-position-x');
            $config[] = "'loading-position-y': " . $this->params->getValue('loading-position-y');
            $config[] = "x: " . $this->params->getValue('x');
            $config[] = "y: " . $this->params->getValue('y');
            if($this->params->checkValue('selectors-effect', 'disabled')) {
                $config[] = "'selectors-effect': false";
            } else {
                $config[] = "'selectors-effect': '" . $this->params->getValue('selectors-effect') . "'";
            }
            $config[] = "'selectors-effect-speed': " . $this->params->getValue('selectors-effect-speed');
            if($this->params->checkValue('show-title', 'disable')) {
                $config[] = "'show-title': false";
            } else {
                $config[] = "'show-title': '" . $this->params->getValue('show-title') . "'";
            }
            if($notCheck) {
                $config[] = "'drag-mode': " . $this->params->getValue('drag-mode');
                $config[] = "'always-show-zoom': " . $this->params->getValue('always-show-zoom');
                $config[] = "'smoothing': " . $this->params->getValue('smoothing');
                $config[] = "'opacity-reverse': " . $this->params->getValue('opacity-reverse');
                $config[] = "'click-to-initialize': " . $this->params->getValue('click-to-initialize');
                $config[] = "'click-to-activate': " . $this->params->getValue('click-to-activate');
                $config[] = "'preload-selectors-small': " . $this->params->getValue('preload-selectors-small');
                $config[] = "'preload-selectors-big': " . $this->params->getValue('preload-selectors-big');
                $config[] = "'zoom-fade': " . $this->params->getValue('zoom-fade');
                $config[] = "'show-loading': " . $this->params->getValue('show-loading');
                $config[] = "'move-on-click': " . $this->params->getValue('move-on-click');
                $config[] = "'preserve-position': " . $this->params->getValue('preserve-position');
                $config[] = "'fit-zoom-window': " . $this->params->getValue('fit-zoom-window');
                $config[] = "'entire-image': " . $this->params->getValue('entire-image');
            } else {
                $config[] = "'drag-mode': " . ($this->params->checkValue('drag-mode', 'Yes') ? 'true' : 'false');
                $config[] = "'always-show-zoom': " . ($this->params->checkValue('always-show-zoom', 'Yes') ? 'true' : 'false');
                $config[] = "'smoothing': " . ($this->params->checkValue('smoothing', 'Yes') ? 'true' : 'false');
                $config[] = "'opacity-reverse': " . ($this->params->checkValue('opacity-reverse', 'Yes') ? 'true' : 'false');
                $config[] = "'click-to-initialize': " . ($this->params->checkValue('click-to-initialize', 'Yes') ? 'true' : 'false');
                $config[] = "'click-to-activate': " . ($this->params->checkValue('click-to-activate', 'Yes') ? 'true' : 'false');
                $config[] = "'preload-selectors-small': " . ($this->params->checkValue('preload-selectors-small', 'Yes') ? 'true' : 'false');
                $config[] = "'preload-selectors-big': " . ($this->params->checkValue('preload-selectors-big', 'Yes') ? 'true' : 'false');
                $config[] = "'zoom-fade': " . ($this->params->checkValue('zoom-fade', 'Yes') ? 'true' : 'false');
                $config[] = "'show-loading': " . ($this->params->checkValue('show-loading', 'Yes') ? 'true' : 'false');
                $config[] = "'move-on-click': " . ($this->params->checkValue('move-on-click', 'Yes') ? 'true' : 'false');
                $config[] = "'preserve-position': " . ($this->params->checkValue('preserve-position', 'Yes') ? 'true' : 'false');
                $config[] = "'fit-zoom-window': " . ($this->params->checkValue('fit-zoom-window', 'Yes') ? 'true' : 'false');
                $config[] = "'entire-image': " . ($this->params->checkValue('entire-image', 'Yes') ? 'true' : 'false');
            }

            $headers = array();
            $headers[] = '<!-- Magic Zoom Magento module version 3.32.3.1 -->';
            $headers[] = '<link type="text/css" href="' . $cssPath . '/magiczoom.css" rel="stylesheet" media="screen" />';
            $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magiczoom.js"></script>';
            $headers[] = '<script type="text/javascript">MagicZoom.options = {' . implode(',' . "\n", $config) . '}</script>';
            return implode("\r\n", $headers);
        }

        function template($params) {
            extract($params);

            if(!isset($alt) || empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
            if(!isset($img) || empty($img)) return false;
            if(!isset($thumb) || empty($thumb)) $thumb = $img;
            if(!isset($id) || empty($id)) $id = md5($img);
            if(!isset($title) || empty($title)) $title = '';
            else {
                $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                if(empty($alt)) $alt = $title;
                $title = " title=\"{$title}\"";
            }
            if(!isset($width) || empty($width)) $width = "";
            else $width = " width=\"{$width}\"";
            if(!isset($height) || empty($height)) $height = "";
            else $height = " height=\"{$height}\"";
            if($this->params->checkValue('show-message', 'Yes')) {
                $message = $this->__($this->params->getValue('message'));
            } else $message = '';

            if(!isset($link) || empty($link)) {
                $link = '';
            } else {
                $link = ' onclick="document.location.href=\'' . ($link) . '\'"';
            }
            $rel = $this->getRel();
            return "<a{$link} class=\"MagicZoom\"{$title} id=\"MagicZoomImage{$id}\" href=\"{$img}\" rel=\"{$rel}\"><img{$width}{$height} src=\"{$thumb}\" alt=\"{$alt}\" /></a><br />" . $message;
        }

        function subTemplate($params) {
            extract($params);

            if(!isset($alt) || empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
            if(!isset($img) || empty($img)) return false;
            if(!isset($medium) || empty($medium)) $medium = $img;
            if(!isset($thumb) || empty($thumb)) $thumb = $img;
            if(!isset($id) || empty($id)) $id = md5($img);
            if(!isset($title) || empty($title)) $title = '';
            else {
                $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                if(empty($alt)) $alt = $title;
                $title = " title=\"{$title}\"";
            }
            if(!isset($width) || empty($width)) $width = "";
            else $width = " width=\"{$width}\"";
            if(!isset($height) || empty($height)) $height = "";
            else $height = " height=\"{$height}\"";

            $rel = $this->getRel();
            return "<a{$title} href=\"{$img}\" rel=\"zoom-id: MagicZoomImage{$id};{$rel}\" rev=\"{$medium}\"><img{$width}{$height} src=\"{$thumb}\" alt=\"{$alt}\" /></a>";
        }

        function addonsTemplate($imgPath = '') {
            /*if ($this->params->checkValue("loading-animation", "Yes")){
                return '<img style="display:none;" class="MagicZoomLoading" src="' . $imgPath . '/' . $this->params->getValue("loading-image") . '" alt="' . $this->params->getValue("loading-text") . '"/>';
            } else return '';*/
             /* if ($this->params->checkValue("show-loading", "Yes")){
                      return '<img style="display:none;" class="MagicZoomLoading" src="' . $imgPath . '/' . $this->params->getValue("loading-image") . '" alt="' . $this->params->getValue("loading-text") . '"/>';
              } else return '';*/
        }

        function getRel() {
            $rel = array();
            if(count($this->general->params)) {
                foreach($this->general->params as $name => $param) {
                    if($this->params->checkValue($name, $param['value'])) continue;
                    switch($name) {
                        case 'opacity':
                            $rel[] = 'opacity: ' . $this->params->getValue('opacity');
                            break;
                        case 'opacity':
                            $rel[] = 'opacity: ' . $this->params->getValue('opacity');
                            break;
                        case 'zoom-width':
                            $rel[] = 'zoom-width: ' . $this->params->getValue('zoom-width');
                            break;
                        case 'zoom-height':
                            $rel[] = 'zoom-height: ' . $this->params->getValue('zoom-height');
                            break;
                        case 'zoom-position':
                            $rel[] = 'zoom-position: ' . $this->params->getValue('zoom-position');
                            break;
                        case 'thumb-change':
                            $rel[] = 'thumb-change: ' . $this->params->getValue('thumb-change');
                            break;
                        case 'smoothing-speed':
                            $rel[] = 'smoothing-speed: ' . $this->params->getValue('smoothing-speed');
                            break;
                        case 'zoom-distance':
                            $rel[] = 'zoom-distance: ' . $this->params->getValue('zoom-distance');
                            break;
                        case 'selectors-mouseover-delay':
                            $rel[] = 'selectors-mouseover-delay: ' . $this->params->getValue('selectors-mouseover-delay');
                            break;
                        case 'zoom-fade-in-speed':
                            $rel[] = 'zoom-fade-in-speed: ' . $this->params->getValue('zoom-fade-in-speed');
                            break;
                        case 'zoom-fade-out-speed':
                            $rel[] = 'zoom-fade-out-speed: ' . $this->params->getValue('zoom-fade-out-speed');
                            break;
                        case 'fps':
                            $rel[] = 'fps: ' . $this->params->getValue('fps');
                            break;
                        case 'loading-msg':
                            $rel[] = 'loading-msg: ' . $this->params->getValue('loading-msg');
                            break;
                        case 'loading-opacity':
                            $rel[] = 'loading-opacity: ' . $this->params->getValue('loading-opacity');
                            break;
                        case 'loading-position-x':
                            $rel[] = 'loading-position-x: ' . $this->params->getValue('loading-position-x');
                            break;
                        case 'loading-position-y':
                            $rel[] = 'loading-position-y: ' . $this->params->getValue('loading-position-y');
                            break;
                        case 'x':
                            $rel[] = 'x: ' . $this->params->getValue('x');
                            break;
                        case 'y':
                            $rel[] = 'y: ' . $this->params->getValue('y');
                            break;
                        case 'selectors-effect':
                            if($this->params->checkValue('selectors-effect', 'disabled')) {
                                $rel[] = 'selectors-effect: false';
                            } else {
                                $rel[] = 'selectors-effect: ' . $this->params->getValue('selectors-effect');
                            }
                            break;
                        case 'selectors-effect-speed':
                            $rel[] = 'selectors-effect-speed: ' . $this->params->getValue('selectors-effect-speed');
                            break;
                        case 'show-title':
                            if($this->params->checkValue('show-title', 'disable')) {
                                $rel[] = 'show-title: false';
                            } else {
                                $rel[] = 'show-title: ' . $this->params->getValue('show-title');
                            }
                            break;
                        case 'drag-mode':
                            $rel[] = 'drag-mode: ' . ($this->params->checkValue('drag-mode', 'Yes') ? 'true' : 'false');
                            break;
                        case 'always-show-zoom':
                            $rel[] = 'always-show-zoom: ' . ($this->params->checkValue('always-show-zoom', 'Yes') ? 'true' : 'false');
                            break;
                        case 'smoothing':
                            $rel[] = 'smoothing: ' . ($this->params->checkValue('smoothing', 'Yes') ? 'true' : 'false');
                            break;
                        case 'opacity-reverse':
                            $rel[] = 'opacity-reverse: ' . ($this->params->checkValue('opacity-reverse', 'Yes') ? 'true' : 'false');
                            break;
                        case 'click-to-initialize':
                            $rel[] = 'click-to-initialize: ' . ($this->params->checkValue('click-to-initialize', 'Yes') ? 'true' : 'false');
                            break;
                        case 'click-to-activate':
                            $rel[] = 'click-to-activate: ' . ($this->params->checkValue('click-to-activate', 'Yes') ? 'true' : 'false');
                            break;
                        case 'preload-selectors-small':
                            $rel[] = 'preload-selectors-small: ' . ($this->params->checkValue('preload-selectors-small', 'Yes') ? 'true' : 'false');
                            break;
                        case 'preload-selectors-big':
                            $rel[] = 'preload-selectors-big: ' . ($this->params->checkValue('preload-selectors-big', 'Yes') ? 'true' : 'false');
                            break;
                        case 'zoom-fade':
                            $rel[] = 'zoom-fade: ' . ($this->params->checkValue('zoom-fade', 'Yes') ? 'true' : 'false');
                            break;
                        case 'show-loading':
                            $rel[] = 'show-loading: ' . ($this->params->checkValue('show-loading', 'Yes') ? 'true' : 'false');
                            break;
                        case 'move-on-click':
                            $rel[] = 'move-on-click: ' . ($this->params->checkValue('move-on-click', 'Yes') ? 'true' : 'false');
                            break;
                        case 'preserve-position':
                            $rel[] = 'preserve-position: ' . ($this->params->checkValue('preserve-position', 'Yes') ? 'true' : 'false');
                            break;
                        case 'fit-zoom-window':
                            $rel[] = 'fit-zoom-window: ' . ($this->params->checkValue('fit-zoom-window', 'Yes') ? 'true' : 'false');
                            break;
                        case 'entire-image':
                            $rel[] = 'entire-image: ' . ($this->params->checkValue('entire-image', 'Yes') ? 'true' : 'false');
                            break;
                    }
                }
            }
            if(count($rel)) {
                $rel = implode(';',$rel) . ';';
            } else {
                $rel = '';
            }
            return $rel;
        }

        function _paramDefaults() {
            $params = array("zoom-width"=>array("id"=>"zoom-width","group"=>"Positioning and Geometry","default"=>"300","label"=>"Zoomed area width (in pixels)","type"=>"num"),"zoom-height"=>array("id"=>"zoom-height","group"=>"Positioning and Geometry","default"=>"300","label"=>"Zoomed area height (in pixels)","type"=>"num"),"zoom-position"=>array("id"=>"zoom-position","group"=>"Positioning and Geometry","default"=>"right","label"=>"Zoomed area position","type"=>"array","subType"=>"select","values"=>array("top","right","bottom","left","inner")),"zoom-distance"=>array("id"=>"zoom-distance","group"=>"Positioning and Geometry","default"=>"15","label"=>"Distance between small image and zoom window (in pixels)","type"=>"num"),"size-depends"=>array("id"=>"size-depends","group"=>"Positioning and Geometry","default"=>"both","label"=>"Size of thumbnail depends on","type"=>"array","subType"=>"select","values"=>array("width","height","both")),"thumb-size"=>array("id"=>"thumb-size","group"=>"Positioning and Geometry","default"=>"250","label"=>"Size of thumbnail (in pixels)","type"=>"num"),"category-thumb-size"=>array("id"=>"category-thumb-size","group"=>"Positioning and Geometry","default"=>"150","label"=>"Size of thumbnail on category pages (in pixels)","type"=>"num"),"square-images"=>array("id"=>"square-images","group"=>"Positioning and Geometry","default"=>"No","label"=>"Allways create square thumbnails","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"drag-mode"=>array("id"=>"drag-mode","group"=>"Zoom mode","default"=>"No","label"=>"Use drag mode?","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"always-show-zoom"=>array("id"=>"always-show-zoom","group"=>"Zoom mode","default"=>"No","label"=>"Always show zoom?","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"move-on-click"=>array("id"=>"move-on-click","group"=>"Zoom mode","default"=>"Yes","label"=>"Click alone will also move zoom (drag mode only)","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"x"=>array("id"=>"x","group"=>"Zoom mode","default"=>"-1","label"=>"Initial zoom X-axis position for drag mode, -1 is center","type"=>"num"),"y"=>array("id"=>"y","group"=>"Zoom mode","default"=>"-1","label"=>"Initial zoom Y-axis position for drag mode, -1 is center","type"=>"num"),"preserve-position"=>array("id"=>"preserve-position","group"=>"Zoom mode","default"=>"No","label"=>"Position of zoom can be remembered for multiple images and drag mode","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"fit-zoom-window"=>array("id"=>"fit-zoom-window","group"=>"Zoom mode","default"=>"Yes","label"=>"Resize zoom window if big image is smaller than zoom window","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"thumb-change"=>array("id"=>"thumb-change","group"=>"Multiple images","default"=>"click","label"=>"Thumb change event","type"=>"array","subType"=>"select","values"=>array("click","mouseover")),"selectors-mouseover-delay"=>array("id"=>"selectors-mouseover-delay","group"=>"Multiple images","default"=>"200","label"=>"Multiple images delay in ms before switching thumbnails","type"=>"num"),"preload-selectors-small"=>array("id"=>"preload-selectors-small","group"=>"Multiple images","default"=>"Yes","label"=>"Multiple images, preload small images","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"preload-selectors-big"=>array("id"=>"preload-selectors-big","group"=>"Multiple images","default"=>"No","label"=>"Multiple images, preload large images","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"selectors-effect"=>array("id"=>"selectors-effect","group"=>"Multiple images","default"=>"dissolve","label"=>"Dissolve or cross fade thumbnail when switching thumbnails","type"=>"array","subType"=>"select","values"=>array("dissolve","fade","disable")),"selectors-effect-speed"=>array("id"=>"selectors-effect-speed","group"=>"Multiple images","default"=>"400","label"=>"Selectors effect speed, ms","type"=>"num"),"use-individual-titles"=>array("id"=>"use-individual-titles","group"=>"Multiple images","default"=>"Yes","label"=>"Use individual image titles for additional images?","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"selector-size"=>array("id"=>"selector-size","group"=>"Multiple images","default"=>"56","label"=>"Size of additional thumbnails (in pixels)","type"=>"num"),"category-selector-size"=>array("id"=>"category-selector-size","group"=>"Multiple images","default"=>"56","label"=>"Size of additional thumbnails on category pages (in pixels)","type"=>"num"),"show-selectors-on-category-page"=>array("id"=>"show-selectors-on-category-page","group"=>"Multiple images","default"=>"No","label"=>"Show selectors on category page","type"=>"array","subType"=>"select","values"=>array("Yes","No")),"click-to-initialize"=>array("id"=>"click-to-initialize","group"=>"Initialization","default"=>"No","label"=>"Click to initialize Magic Zoom and download large image","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"click-to-activate"=>array("id"=>"click-to-activate","group"=>"Initialization","default"=>"No","label"=>"Click to show the zoom","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"show-loading"=>array("id"=>"show-loading","group"=>"Initialization","default"=>"Yes","label"=>"Loading message","type"=>"array","subType"=>"select","values"=>array("Yes","No")),"loading-msg"=>array("id"=>"loading-msg","group"=>"Initialization","default"=>"Loading zoom...","label"=>"Loading message text","type"=>"text"),"loading-opacity"=>array("id"=>"loading-opacity","group"=>"Initialization","default"=>"75","label"=>"Loading message opacity (0-100)","type"=>"num"),"loading-position-x"=>array("id"=>"loading-position-x","group"=>"Initialization","default"=>"-1","label"=>"Loading message X-axis position, -1 is center","type"=>"num"),"loading-position-y"=>array("id"=>"loading-position-y","group"=>"Initialization","default"=>"-1","label"=>"Loading message Y-axis position, -1 is center","type"=>"num"),"entire-image"=>array("id"=>"entire-image","group"=>"Initialization","default"=>"No","label"=>"Show entire large image on hover","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"show-title"=>array("id"=>"show-title","group"=>"Title","default"=>"top","label"=>"Show the title of the image in the zoom window","type"=>"array","subType"=>"select","values"=>array("top","bottom","disable")),"opacity"=>array("id"=>"opacity","group"=>"Effects","default"=>"50","label"=>"Square opacity","type"=>"num"),"smoothing"=>array("id"=>"smoothing","group"=>"Effects","default"=>"Yes","label"=>"Enable smooth zoom movement","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"smoothing-speed"=>array("id"=>"smoothing-speed","group"=>"Effects","default"=>"40","label"=>"Speed of smoothing (1-99)","type"=>"num"),"opacity-reverse"=>array("id"=>"opacity-reverse","group"=>"Effects","default"=>"No","label"=>"Add opacity to background instead of hovered area","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"zoom-fade"=>array("id"=>"zoom-fade","group"=>"Effects","default"=>"No","label"=>"Zoom window fade effect","type"=>"array","subType"=>"select","values"=>array("Yes","No")),"zoom-fade-in-speed"=>array("id"=>"zoom-fade-in-speed","group"=>"Effects","default"=>"200","label"=>"Zoom window fade-in speed (in milliseconds)","type"=>"num"),"zoom-fade-out-speed"=>array("id"=>"zoom-fade-out-speed","group"=>"Effects","default"=>"200","label"=>"Zoom window fade-out speed  (in milliseconds)","type"=>"num"),"fps"=>array("id"=>"fps","group"=>"Effects","default"=>"25","label"=>"Frames per second for zoom effect","type"=>"num"),"show-message"=>array("id"=>"show-message","group"=>"Miscellaneous","default"=>"Yes","label"=>"Show message under image?","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"message"=>array("id"=>"message","group"=>"Miscellaneous","default"=>"Move your mouse over image","label"=>"Message under images","type"=>"text"),"use-effect-on-product-page"=>array("id"=>"use-effect-on-product-page","group"=>"Miscellaneous","default"=>"Yes","label"=>"Use effect on product page","type"=>"array","subType"=>"select","values"=>array("Yes","No")),"use-effect-on-category-page"=>array("id"=>"use-effect-on-category-page","group"=>"Miscellaneous","default"=>"No","label"=>"Use effect on category page","type"=>"array","subType"=>"select","values"=>array("Yes","No")),"ignore-magento-css"=>array("id"=>"ignore-magento-css","group"=>"Miscellaneous","default"=>"No","label"=>"Ignore magento CSS width/height styles for additional images","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"option-associated-with-images"=>array("id"=>"option-associated-with-images","group"=>"Miscellaneous","default"=>"color","label"=>"Options names associated with images separated by commas (e.g 'Color,Size')","description"=>"You should named all product images associated with option values. e.g If option values is 'red', 'blue' and 'white' then you should have 3 images with labels 'red', 'blue' and 'white'","type"=>"text"),"image-magick-path"=>array("id"=>"image-magick-path","group"=>"Miscellaneous","default"=>"/usr/bin","label"=>"Image magick binaries path","type"=>"text"));
            $this->params->appendArray($params);
        }
    }

}
?>
