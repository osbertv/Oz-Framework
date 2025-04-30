<?php 
/**
 +-----------------------------------------------------------+ 
 Standard class implementation
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		OV_StdClass
 @filesource 	system/OV_StdClass.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;
/**
 * Standard class implementation
 * @package OV_StdClass
 */
//$class_instances = array();
class View
{
    public $view_folder = APPPATH.'Resource/view/';
    public $file;
    public $layout;
    private $_section = [];
    public $sections = [];
    private $_data;
    public $request;
    
    function __construct($file = false, $data = [], $layout = 'default' )
    {
        $this->layout = $layout;
        $this->request = Request::getInstance();
        if ($file !== false) {
            $this->file = $file;
            $this->template($file, $this->_data = $data);
        }
    }
    
    private function view_file($view) {
        $filename = $this->view_folder.$view.EXT;
        if (!file_exists($filename)) {
            debug('app view file not found '.$filename, 255);
            $filename = SYSPATH . 'Resource/view/' .$view . EXT;
        }
        return $filename;
    }
    
    function include($file, $data = []) {
        if (is_array($data)) {
            foreach ($data as $___data_key => $___data_value) {
                ${$___data_key} = $___data_value;
            }
        }
        $file = $this->view_file($file);
        debug('Including file '.$file, 255);
        if (file_exists($file)) {
            ob_start();
            include($file);
            $str = ob_get_contents();
            ob_end_clean();
            return $str;
        }
        debug('Include file not found! '.$file, 255);
    }

    function section($name) {
        $this->_section[] = $name;
        if (!isset($this->sections[$name])) $this->sections[$name] = '';
        ob_start();
    }
    
    function section_end() {
        if ( is_array($this->_section) ) {
            $name = array_pop($this->_section);
            $this->sections[$name] .= ob_get_contents();
            ob_end_clean();
            return $name;
        }
        return false;
    }
    
    function view($file, $data = [], $layout = false) {
        $view = new View($file, $data, $layout);
        return $view->render($data);
    }
  
    function template($file, $data = [], $default_section = 'content') {
        //$flash = flash();
        //$session = session();
        if (is_array($data)) {
            foreach ($data as $___data_key => $___data_value) {
                ${$___data_key} = $___data_value;
            }
        }
        $file = $this->view_file($file);
        debug('Including view '.$file, 255);
        if (file_exists($file)) {
            $this->section($default_section);
            include($file);
            return $this->sections[$this->section_end()];
        }
        debug('Include file not found! '.$file, 255);
        return null;
    }
    
    function get_layout($file = false, $data = []) {
        if ( $file === false ) {
            return false;
        }
        return $this->template('layout/'.$file, $data, 'layout');
        //if ( isset($this->sections['layout']) ) {
        //    $this->sections['layout'];
        //}
        //return $view;
    }
    
    function prepare_object_for_replacement($data, &$search, &$replace, $object_string = '',$max_level = 3,  $level = 0){
        if (is_object($data) || is_array($data) ) {
            foreach ($data as $k => $v) {
                if (is_object($v) || is_array($v) ) {
                    if ($level <= $max_level) {
                       $this->prepare_object_for_replacement($v, $search, $replace, $object_string.$k.'.',$max_level,++$level);
                    }
                } else {
                    $search[] = "{{".$object_string.$k."}}";
                    $replace[] = $v;
                }
            }
            return true;
        }
        return false;
    }
    
    public function render($data = [], $layout =  false) {
        if (is_array($data)) {
            foreach ($data as $___key => $___value) {
                ${$___key} = $___value;
            }
        }
        if (! $layout ) {
            $layout = $this->layout;
        }
        $output = '';
        $this->get_layout($layout, $data);
        if ( isset($this->sections['layout']) ) {
            $output .= $this->sections['layout'];
            unset($this->sections['layout']);
        }
        if ( isset($this->sections['']) ) {
            $output .= $this->sections[''];
            unset($this->sections['']);
        }
        if ( strlen($output) > 8 ) {
            foreach ($this->sections as $k => $v) 
                $output = str_replace('<!--'.$k.'-->', $v, $output);
        } else {
            foreach ($this->sections as $k => $v)
                $output .= $v;
        }
        $this->prepare_object_for_replacement($data, $search, $replace);
        return str_replace($search, $replace, $output);
    }

    
    function __toString() {
        return $this->render($this->_data);
    }
    
}
