<?php

/* 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		Flash Notifications
 @filesource            
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 */

namespace Oz;
class Flash extends OzClass
{
    public $errors = [];
    public $toasts = [];
    public $alerts = [];
    
    function error($key) {
        if (func_num_args() > 1) {
            return $this->errors[$key] = func_get_arg(1);
        }
        if (isset($this->errors[$key])) {
            return $this->errors[$key];
        }
    }

    function toast($key) {
        if (func_num_args() > 1) {
            $this->toasts[$key][] = func_get_arg(1);
        }
        if (isset($this->toasts[$key])) {
            return $this->toasts[$key];
        }
    }
    
    function alert($key) {
        if (func_num_args() > 1) {
            return $this->alerts[$key][] = func_get_arg(1);
        }
        if (isset($this->alerts[$key])) {
            return $this->alerts[$key];
        }
    }
    
}