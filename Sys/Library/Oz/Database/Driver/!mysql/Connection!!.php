<?php
/**
 +-----------------------------------------------------------+ 
 MySQL Database driver class implementation
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		ovDatabase_mysql
 @filesource 	system/library/database/driver/mysql.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/
namespace OV\database\driver\mysql;

use \PDO;
//use PDOException;


Class Connection extends Builder
{

	private $driver	= "mysql";
	private $host	= "localhost";
	private $port	= "3306";
	private $username;
	private $password;
	private $database;
		
	//PDO handle
        private $conn = null;
	
	//query handle
	//private $result;

    public function __construct($param = FALSE)
    {
		if ( isset($param['host']) )	$this->host = $param['host'];
		if ( isset($param['port']) )	$this->port = $param['port'];
		if ( isset($param['username']) ) $this->username = $param['username'];
		if ( isset($param['password']) ) $this->password = $param['password'];
		if ( isset($param['database']) ) $this->database = $param['database'];
                $this->connect();
    }


	/**
	 * Connect to server and open database
	 */
    public function connect()
    {
		if ($this->conn) 
			return $this->conn;
		
		$port = ($this->port) ? ';port='.$this->port : '';

		$dsn = "{$this->driver}:host={$this->host}$port;dbname={$this->database}";
		try {
		    $this->conn = new PDO($dsn, $this->username, $this->password);
			//$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		} catch (PDOException $e) {
			trigger_error($e->getMessage());
		}
                parent::__construct($this->conn);
                return $this->conn;
	}

	/**
	 * disconnect from server and close database
	 */
        public function disconnect()
        {
            $this->result = null;
            $this->conn = null;
        }
}
