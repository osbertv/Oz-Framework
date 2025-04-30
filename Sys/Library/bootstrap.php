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

function writelog($str, $logfile = FALSE) {
    static $file;
    if ($logfile!==FALSE) {
        $file = $logfile;
    }
    if (empty($file)) {
        $file = ini_get("error_log");
    }
    //$remote_addr = (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
    $f = @fopen($file,'a');
    @fwrite($f,date("YmdHis")." ".REMOTE_ADDR." ".session_id()." $str\r\n");
    @fclose($f);
}

function debug($message, $level = 1) {
    global $debug;
    if ( ($debug >= $level) || ($debug === TRUE) || $level === TRUE ) {
        writelog(print_r($message, TRUE));
    }
}

function loadClass($className) {
    $file = str_replace("\\", "/", $className);
    $appFileName = ROOTPATH . $file . EXT;
    if (file_exists($appFileName)) {
        require $appFileName;
        return TRUE;
    }

    $libFileName = APPLIBPATH . $file . EXT;
    if (file_exists($libFileName)) {
        require $libFileName;
        return TRUE;
    }
    
    $sysFileName = SYSLIBPATH . $file . EXT;
    if (file_exists($sysFileName)) {
        require $sysFileName;
        return TRUE;
    }
    
    debug('Class "'.$className.'" does not exist.', 0);
}
spl_autoload_register('loadClass'); // Registers the autoloader

require_once 'helper'.EXT;
