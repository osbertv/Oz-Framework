<?php

/* 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		
 @filesource            App/Model/Inbox.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 */
namespace Oz\Database;

class Config
{
	public $driver = 'mysql';	
	public $host = '127.0.0.1';	
	public $port = '3306';
	public $database;	
	public $username;	
	public $password;        
        public $prefix = '';
}