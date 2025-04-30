<?php

/*
  +-----------------------------------------------------------+
  Oz Framework - Simple, Fast.
  +-----------------------------------------------------------+
  @Copyright		(c) 2012 - present, Osbert Villanueva
  @Package
  @filesource            App/Config/*.php
  +-----------------------------------------------------------+
  @Author		Osbert Villanueva <osbertv@gmail.com>
  +-----------------------------------------------------------+
 */

namespace App\Config;


class databaseConfig extends \Oz\Database\Config
{
	public $driver = 'mysql';
	public $host = '127.0.0.1';
	public $port = '3306';
	public $database = 'test';
	public $username = 'root';
	public $password = 'password';
}
