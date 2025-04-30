<?php
/**
 +-----------------------------------------------------------+ 
 | Oz Controller Class
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		entry point to Oz class
 @filesource            System/Oz_Controller.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;


class Controller extends Response
{
    public $allowed_methods = [];
    public $allow_guest = false;
    
    protected function allowed_method() {
        $method = App::$request->getControllerMethod();
        return in_array($method, $this->allowed_methods);
    }
    
    protected function authorized($authenticate = false) {
        if ($this->allow_guest) return true;
        if ( (! session()->Username) && (! $this->allowed_method())  ) {
            if (! session()->URL_Before_Login && !request()->isAjax) {
               session()->URL_Before_Login = request()->server('REQUEST_URI');
            }
            if ($authenticate) {
                echo "<script>location.href='".INDEXPATH."auth/login"."'</script>";
                exit;
                header("Location: ".INDEXPATH."auth/login");
                exit;
            }
            return false;
        }
        return true;
    }
        
    function index() {
        //default function
    }
    	
}