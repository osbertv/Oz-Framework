<?php 
/**
 +-----------------------------------------------------------+ 
 HTTP Request class implementation
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		ovRequest
 @filesource 	system/library/ovRequest.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;

/**
 * @package ovRequest Class
 */
class Request Extends OzClass
{
	
    public $params;
    private $post_params;
    public $url = [];
    public $paths = [];
    public $mime = [
        'js' => 'text/javascript',
        'css' => 'text/css',
        'bmp' => 'image/bmp',
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
    ];
    public $controller_class;
    public $isAjax;
    
    function __construct()
    {
        parent::__construct();   
        //$this->application = App::getInstance();
        $this->_getParams();
        $this->isAjax = ($this->server('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest");
        $this->url = parse_url($this->server('REQUEST_URI'));
        $this->return_if_file();
        $this->url['real_path'] = substr($this->url['path'], strlen(INDEXPATH));
    }
    
    function getController() {
        if (strlen($this->url['real_path']) > 0) {
            $this->paths = explode('/',$this->url['real_path']);
            $controller = ucfirst($this->paths[0]);
            if ($this->paths[0] == substr($_SERVER['SCRIPT_NAME'],1)) {
                array_shift($this->paths);
                if (count($this->paths)) {
                    $controller = ucfirst($this->paths[0]);
                } else {
                    $controller = DEFAULTCONTROLLER;
                }
            }
        } else {
            $controller = DEFAULTCONTROLLER;
        }
        //var_dump($this);
        $this->controller_class = "App\\Controller\\$controller";
        if (class_exists($this->controller_class)) {
            return $this->controller_class::getInstance();
        }
        debug("Error loading controller. [$controller] -> ". print_r($this->url, TRUE));
        header(filter_input(INPUT_SERVER, "SERVER_PROTOCOL")." 404 Not Found");
        exit;
    }
    
    function server($var) {
        return filter_input(INPUT_SERVER,$var);
    }

    function getControllerMethod() {
        if ( isset($this->paths[1]) ) {
            if ( method_exists($this->controller_class, $this->paths[1]) ) {
                return $this->paths[1];
            }
            //return $this->paths[1];
        }
        return 'index';
    }
    
    function content_type($file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (isset($this->mime[$ext])) {
            return $this->mime[$ext];
        }
        return mime_content_type($file);
    }

    function return_if_file($exit = TRUE) {
        $file = ROOTPATH.'public'.$this->url['path'];
        if (is_file($file) && !substr($file,-4)=='.php') {
            debug("Requested file :" . $file);
            //header('date: '.date(DATE_RFC822));
            header('last-modified: '.date(DATE_W3C,filemtime($file)));
            header('cache-control: public, max-age=31536000');
            header('content-lenth: '.filesize($file));
            header('content-type: '.$this->content_type($file));
            print file_get_contents($file);
            if ($exit) exit(0);
        }
    }
        
    private function _getParams()
    {
            $this->params = array();
            foreach ($_POST as $k => $v) {
                $this->_setParam ($k, $v);
                $this->_setPostParam ($k, $v);
            }
            foreach ($_GET as $k => $v)
                $this->_setParam ($k, $v);
            return $this->params;
    }
    
    function _setParam($key, $value) {
        $this->params[$key] = $value;
        if (is_array($value))
            foreach ($value as $k => $v) 
                $this->_setParam ($key.".".$k, $v);
    }

    function _setPostParam($key, $value) {
        $this->post_params[$key] = $value;
        if (is_array($value))
            foreach ($value as $k => $v) 
                $this->_setPostParam ($key.".".$k, $v);
    }
    
    function param($name, $def=false, $useCookie=false)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        if (isset($_COOKIE[$name]) && $useCookie) {
            return filter_input(INPUT_COOKIE, $name);
        }
        return $def;
    }

    function posted($name = false, $def=null)
    {
        if (!$name ) {
            return $this->post_params;
        }
        if (isset($this->post_params[$name])) {
            return $this->post_params[$name];
        }
        return $def;
    }
    
    function path() {
        if (func_num_args()) {
            $index = func_get_arg(0);
            return (isset($this->paths[$index])) ? $this->paths[$index] : false;
        }
        return $this->url['real_path'];
    }

    function referer() {
        return $this->server('HTTP_REFERER');
    }
}
