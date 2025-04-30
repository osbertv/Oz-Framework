<?php
/**
 +-----------------------------------------------------------+ 
 Application entry point
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 , Osbert Villanueva 
 @Package		main entry point to application
 @filesource            index.php
 +-----------------------------------------------------------+ 
 @Author                Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/


// Start timestamp
        $oz_app_start = $_SERVER["REQUEST_TIME_FLOAT"] ?? gettimeofday(true);

// Change this to your timezone
	date_default_timezone_set('Asia/Manila');  

//PHP ERROR REPORTING LEVEL
	//error_reporting(0);
	error_reporting(E_ALL);

// root url
	$index_path = "/"; 

// Application name
        $application_name = "Oz Framework";
        
//dump debug messages (true, 1-10)
	//$debug = TRUE;
        $debug = 1;

//default country code
	define("COUNTRY_CODE","63");

//controller
        define('DEFAULTCONTROLLER', 'Home');








/*
|===============================================================
| END OF USER CONFIGURABLE SETTINGS
|===============================================================
*/

$root_path = str_replace("\\", "/", realpath(dirname(__FILE__)).'/../' ); 
defined('ROOTPATH') or define('ROOTPATH', $root_path );
defined('INDEXPATH') or define('INDEXPATH', $index_path );
defined('APP_NAME') or define('APP_NAME', $application_name);


/*
|---------------------------------------------------------------
| DEFINE APPLICATION CONSTANTS
|---------------------------------------------------------------
| BASEPATH	- The full server path to the "system" folder
| APPPATH	- The full server path to the "application" folder
| LIBPATH	- The full server path to the "library" folder
**/

define('BASEPATH', dirname(ROOTPATH));
define('DEBUG', $debug);
define('EXT', '.php');
define('SYSPATH', ROOTPATH.'Sys/');
define('LIBPATH', SYSPATH.'Library/');
define('SYSLIBPATH', SYSPATH.'Library/');
define('APPPATH', ROOTPATH.'App/');
define('APPLIBPATH', APPPATH.'Library/');

//Error log file to application log path
ini_set("error_log", ROOTPATH."Logs/".Date("Ymd").".log");

define('ALLOWGUEST', $allow_guest ?? FALSE);
define('REMOTE_ADDR', (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);

require SYSLIBPATH."bootstrap".EXT;

Oz\App::run();
