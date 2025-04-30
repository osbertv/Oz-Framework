<?php
/**
 +-----------------------------------------------------------+ 
 HTTP Response class implementation
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		ovResponse
 @filesource 	system/library/ovResponse.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;

use \Oz\View as View;
/**
 * @package ovResponse Class
 */
class Response Extends OzClass
{
    /*
        public $contentType = 'text/html';
	public $charset = 'UTF-8';
	public $contentExpiry = '0';
	public $expire = '-1';
	public $pragma = 'no-cache';
	public $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	public $xmlns = 'http://www.w3.org/1999/xhtml';
	public $title = 'Untitled';

	private $body;
	private $head;
	private $foot;
	private $link;
    */
    public $parent;
    public $children;
    public $layout = 'default';
    public $view_folder = APPPATH.'Resource/view/';
    public $name;
    private $_data;
    public $view;
    public $request;
    
    function __construct()
    {
        //if ($view !== false) {
        //    $this->name = $view;
        //    $this->view = new View($file, $data, $this->layout = $layout);
        //}
        $this->request = Request::getInstance();
    }
  
    function view() {
        $arg = func_get_args();
        $data = isset($arg[1]) ? $arg[1] : $this->_data;
        $layout = isset($arg[2]) ? $arg[2] : (($this->request->isAjax) ? '' : $this->layout);
        debug ("New View($arg[0], data, $layout)", 10);
        return new View($arg[0], $data, $layout);
    }
    
    
    function __toString() {
        return $this->view;
    }
  
    function redirect($url) {
        header("location: $url");
        exit;
    }

    function redirect_referer() {
        $this->redirect($this->request->referer());
    }
    
}
