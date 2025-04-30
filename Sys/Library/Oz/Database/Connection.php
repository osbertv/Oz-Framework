<?php

/* 
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

namespace Oz\Database;

class Connection extends \Oz\OzClass {

    private $driver;
    public  $driver_class = "mysql";
    public  $config;
    
    //initialize database driver and connection
    public function __construct($param = array('config'=>'database'))
    {
        parent::__construct();
        if ( isset($param['config']) )
        {
            $configClass = "App\\Config\\".$param['config']."Config";
            $this->config = new $configClass();
            foreach (\get_object_vars($this->config) as $k => $v) {
                $param[$k] = $v;
            }
        }
        if ( isset($param['driver']) ) $this->driver_class = $param['driver'];
        $dbdriver = "\\Oz\\Database\\Driver\\".$this->driver_class;
        $this->driver = new $dbdriver($param);
        //$this->handle->primary_key = $this->primary_key;
    }

    
    public function handle()
    {
        return $this->driver;
    }
    
    public function PDO()
    {
        return $this->driver->PDO();
    }


    //connect to database
    //public function connect()
    //{
    //        return $this->handle->connect();
    //}

    //disconnect to database
    //public function disconnect()
    //{
    //        return $this->handle->disconnect();
    //}
   

    function __call($name, $arguments ) {
        return call_user_func_array(array($this->driver,$name), $arguments);
//        $ret = FALSE;
//        switch (count($arguments)) {
//            case 0: $ret = $this->driver->$name();break;
//            case 1: $ret = $this->driver->$name($arguments[0]);break;
//            case 2: $ret = $this->driver->$name($arguments[0],$arguments[1]);break;
//            case 3: $ret = $this->driver->$name($arguments[0],$arguments[1],$arguments[2]);break;
//            case 4: $ret = $this->driver->$name($arguments[0],$arguments[1],$arguments[2],$arguments[3]);break;
//            default: $ret = $this->driver->$name($arguments);
//        }
//        return $ret;
    }
    
}
