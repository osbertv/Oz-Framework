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

namespace Oz;

class Model extends Database\Table {
    public static function __callStatic($name, $arguments) {
        $class = get_called_class();
        switch (count($arguments)) {
            case 0:
                return $class::getInstance()->$name();
            case 1:
                return $class::getInstance()->$name($arguments[0]);
            case 2:
                return $class::getInstance()->$name($arguments[0],$arguments[1]);
            case 3:
                return $class::getInstance()->$name($arguments[0],$arguments[1],$arguments[2]);
            case 4:
                return $class::getInstance()->$name($arguments[0],$arguments[1],$arguments[2],$arguments[4]);
            default:
                return call_user_func_array(array($class::getInstance(),$name), $arguments);
            }
    }
}
