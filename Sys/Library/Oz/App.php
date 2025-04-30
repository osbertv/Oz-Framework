<?php

/* 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		Application Main Class
 @filesource            
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 */



namespace Oz;


class App extends OzClass {
    
    static $session;
    static $request;
    //static $response;
    static $controller;
        
    static function run() {
        //self::$session = session();
        $request = App::$request = request();
        App::$controller = $request->getController();
        $method = $request->getControllerMethod();
        echo call_user_func_array(array(App::$controller, $method), array_slice($request->paths,2) );
//        return;
//        switch (count($request->paths)) {
//            case 0:
//            case 1:
//            case 2:
//                echo App::$controller->$method();
//                break;
//            case 3:
//                echo App::$controller->$method($request->paths[2]);
//                break;
//            case 4:
//                echo App::$controller->$method($request->paths[2], $request->paths[3] );
//                break;
//            case 5:
//                echo App::$controller->$method($request->paths[2], $request->paths[3], $request->paths[4]);
//                break;
//            case 6:
//                echo App::$controller->$method($request->paths[2], $request->paths[3], $request->paths[4], $request->paths[5]);
//                break;
//            default:
//                echo App::$controller->$method($request->paths);
//        }
    }
    
}

