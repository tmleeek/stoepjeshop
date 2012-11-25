<?php

@ini_set('memory_limit', '512M');

if(!function_exists('lcfirst')) {
    function lcfirst($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}

if(!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string,$style=ENT_COMPAT) {
        $translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS,$style));
        if($style === ENT_QUOTES){ $translation['&#039;'] = '\''; }
        return strtr($string,$translation);
    }
}

if(!in_array('MagicToolboxParams', get_declared_classes())) {

    class MagicToolboxParams {
        var $params;

        function MagicToolboxParams() {
            $this->params = array();
        }

        function append($id, $value) {
            if(!is_array($value)) {
                $this->params[$id]["value"] = $value;
            } else {
                foreach($value as $k => $v) {
                    $this->params[$id][$k] = $v;
                }
            }
        }

        function appendArray($params) {
            foreach($params as $key => $param) {
                $this->append($key, $param);
            }
        }

        function get($id) {
            return isset($this->params[$id]) ? $this->params[$id] : false;
        }

        function set($id, $value) {
            $this->params[$id]['value'] = $value;
        }

        function getValue($id) {
            $p = $this->get($id);
            if($p) {
                return isset($p['value']) ? $p['value'] : $p['default'];
            } else return false;
        }

        function getValues($id) {
            $p = $this->get($id);
            if($p) {
                return isset($p['values']) ? $p['values'] : array($p['default']);
            } else return false;
        }

        function checkValue($id, $value = false) {
            if(!is_array($value)) $value = array($value);
            return in_array(strtolower($this->getValue($id)), array_map('strtolower', $value));
        }

        function getArray() {
            return $this->params;
        }

        function getNames() {
            return array_keys($this->params);
        }

        function clear() {
            $this->params = array();
        }

        function loadINI($file) {
            if(!file_exists($file)) return false;
            $ini = file($file);
            foreach($ini as $num=> $line) {
                $line = trim($line);
                if(empty($line) || in_array(substr($line, 0, 1), array(';','#'))) continue;
                $cur = explode('=', $line);
                if(count($cur) != 2) {
                    error_log("WARNING: You have errors in you INI file ({$file}) on line " . ($num+1) . "!");
                    continue;
                }
                $this->set(trim($cur[0]), trim($cur[1]));
            }
            return true;
        }

    }

}
?>
