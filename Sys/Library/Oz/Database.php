<?php
/**
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012, Osbert Villanueva 
 @filesource 	system/library/ovDatabase.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
**/

namespace Oz;


/**
 * Standard class implementation
 * @package ovDatabase
 */
class Database extends \Oz\Database\Connection
{
    public function __construct($param = array('config'=>'database'))
    {
        parent::__construct($param);
    }
}
