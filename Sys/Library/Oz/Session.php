<?php
/**
 +-----------------------------------------------------------+ 
 SESSION class implementation
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		ovSession
 @filesource 	system/library/ovSession.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;

/**
 * @package ovSession Class
 */
class Session Extends OzClass
{
        public $application;
        public $id;

	public function __construct()
	{
            //ini_set("session.save_handler", "memcached");
            //ini_set("session.save_path", "localhost:11211");
            if (func_num_args()) {
                session_id(func_get_arg(0));
            }
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $this->id = session_id();
            parent::__construct();
	}

	public function login($username, $password = FALSE)
	{
		$users = database()->query('select * from users where username=:Username ', array('Username'=>$username), true);
		if (count($users) == 0) return false;
		
		if ($password !== FALSE) {
		    if (! password_verify($password, $users[0]['password']) ) return false;
                }
		
		$this->UserId = $users[0]['id'];
		$this->Username = $users[0]['username'];
		$this->Email = $users[0]['email'];
                $this->Fullname = $users[0]['fullname'];
                $this->Role = $users[0]['role'];
                $this->User = $users[0];
		return TRUE;		
	}
        
        function logout() {
            $this->UserId = NULL;
            $this->Username = NULL;
            $this->Email = NULL;
            $this->Fullname = NULL;
            $this->Role = NULL;
            $this->User = NULL;
            //session_destroy(); //osbert :2021-05-12
            return TRUE;		
        }

        function is_loggedIn() {
            return ($this->UserId);
        }
        
        function is_admin() {
            return ($this->Role == 'admin');
        }
        
        function is_role($role) {
            return ($this->Role == $role);
        }
        
	public function __get($prop)
	{
		return $_SESSION[$prop] ?? FALSE;
	}

	public function __set($prop, $value)
	{
		return $_SESSION[$prop] =  $value;
	}

        public function hash($value, array $options = ['cost' => 10])
        {
            $hash = password_hash($value, PASSWORD_BCRYPT, $options);
            return $hash;
        }
        
        
}
