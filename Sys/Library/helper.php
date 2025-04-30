<?php

/** 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		
 @filesource            
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 */


/**
 * Application session object
 * @return Oz\Session
 */
function session() {
    static $session = null;
    if (empty($session)) {
        $session = Oz\Session::getInstance();
    }
    switch (func_num_args()) {
        case 0: return $session; break;
        case 1: return $session->{func_get_arg(0)}; break;
        default: return $session->{func_get_arg(0)} = func_get_arg(1);
    }
    return $session;
}



/**
 * Http request object
 * @return Oz\Request
 */
function request() {
    static $request = null;
    if (empty($request)) {
        $request = Oz\Request::getInstance();
    }
    switch (func_num_args()) {
        case 0: return $request; break;
        case 1: return $request->param(func_get_arg(0)) ; break;
        case 2: return $request->param(func_get_arg(0),func_get_arg(1)); break;
        default: return $request->param(func_get_arg(0),func_get_arg(1),func_get_arg(2));
    }
    return $request;
}



/**
 * Default database connection or if $param is defined
 * @param array $param connects to a database defined in ['config'=> {name} ] found in App/Config
 * @return Oz\Database
 */
function database() {
    if (func_num_args()) {
        return Oz\Database::getInstance(func_get_arg(0));
    }
    return Oz\Database::getInstance();
}


/**
 * Include a view resource file
 * @param string $file - the view file
 * @param mixed $data - passed variables
 * @param string $layout - layout file
 */
function view($file, $data = [], $layout = false) {
    return new \Oz\View($file , $data, $layout);
}


/**
 * Flash static instance
 * @return Oz\Flash
 */
function flash() {
    return Oz\Flash::getInstance();
}

if (!function_exists('str_intval')) {
    /**
     * return only the number char/values of a string
     * @param string $str
     * @return int
     */
    function str_intval($str) {
        $a = str_split($str);
        $s = "";
        $n = ["0","1","2","3","4","5","6","7","8","9"];
        foreach ($a as $v) {
            if (in_array($v, $n)) {
                $s .= $v;
            }
        }
        return $s;
    }
}

if (!function_exists('blank')) {
    /**
     * Check if $var is blank or has no value
     * @param mixed $var
     * @param optional $default returned value if blank is true or returns $var if false
     * @return boolean if $default is not set otherwise returns $var if true.
     */
    function blank($var) {
        if ( $var === null || $var === "" || $var === false) 
            return (func_num_args() > 1) ? func_get_arg(1) : true;
        return (func_num_args() > 1) ? $var : false;
    }
}

if (!function_exists('utime')) {
    /**
     * return time with microseconds
     * @return float
     */
    function utime() {
        return gettimeofday(true);
    }
}

if (!function_exists('form_method')) {
    /**
     * sets or return [input] for form method POST,GET,PUT,DELETE
     * @return string
     */
    function form_method() {
        if (!func_num_args()) {
            return request()->param('_form_method_','GET');
        }
        $method = strtoupper(func_get_arg(0));
        return "<input type='hidden' name='_form_method_' value='$method' />";
    }
}

if (!function_exists('clean_string')) {
    /**
     * remove special characters and replace with param1
     * @return string
     */
    function clean_string($string, $replace_char = '') {
        $string = str_replace(' ', $replace_char, $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', $replace_char, $string); // Removes special chars.
        //return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return $string;
    }
}
