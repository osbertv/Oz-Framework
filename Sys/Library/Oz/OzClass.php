<?php 
/**
 +-----------------------------------------------------------+ 
 Standard class implementation
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		Oz_StdClass
 @filesource 	system/Oz_StdClass.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;
/**
 * Standard class implementation
 * @package Oz_StdClass
 */
//$class_instances = array();
class OzClass
{

    //public $application;
    
    public function __construct()
    {
        debug(get_called_class()."::__construct()", 10);
    }

    /**
     * Singleton object instance
     */
    public static $class_instances = [];
    public static function getInstance($param = FALSE)
    {
        //if $param is set return new instance of class
        $class = get_called_class();
        if ($param !== FALSE) {
            $obj = new $class($param);
            return $obj;
        }
        //global $class_instances;
        //static $class_instances = array();
        if ( ! isset(OzClass::$class_instances[$class]) ) {
            debug("Creating new '$class' instance", 10);
            OzClass::$class_instances[$class] = new $class();
        }
        return OzClass::$class_instances[$class];
    }


    
    /**
     * Function _new
     * @param string $param = construct parameter/s
     * 
     * @return Class
     */

    public function _new($param = FALSE)
    {
        $c = get_called_class();
        switch (func_num_args()) {
            case 0: return new $c();
            case 1: return new $c(func_get_arg(0));
            case 2: return new $c(func_get_arg(0),func_get_arg(1));
            case 3: return new $c(func_get_arg(0),func_get_arg(1),func_get_arg(2));
        }
    }

    public static function new() {
        $c = get_called_class();
        switch (func_num_args()) {
            case 0: return new $c();
            case 1: return new $c(func_get_arg(0));
            case 2: return new $c(func_get_arg(0),func_get_arg(1));
            case 3: return new $c(func_get_arg(0),func_get_arg(1),func_get_arg(2));
        }
    }
    
    function __destruct()
    {
        global $oz_app_start;
        $oz_app_took = gettimeofday(true)-$oz_app_start;
        debug(get_called_class()."::__destruct() execution elapsed $oz_app_took ms", 255);
    }

}


