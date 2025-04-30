<?php

namespace Oz\Database\Driver;
use \PDO;
use \Oz\Database\Record;
use \Oz\Database\Records;
trait Driver {

    public $database;
    private $driver,$host,$port,$dsn;
    private $username,$password;
    private $table;
    private $dbh = null, $columns, $sql, $bindValues, $getSQL,
	$where, $orWhere, $whereCount=0, $isOrWhere = false,
	$rowCount=0, $limit, $orderBy, $lastIDInserted = 0;

    // Initial values for pagination array
    private $pagination = ['previousPage' => null,'currentPage' => 1,'nextPage' => null,'lastPage' => null, 'totalRows' => null];

    function __construct( $param )
    {
        if ( isset($param['host']) )	$this->host = $param['host'];
        if ( isset($param['port']) )	$this->port = $param['port'];
        if ( isset($param['username']) ) $this->username = $param['username'];
        if ( isset($param['password']) ) $this->password = $param['password'];
        if ( isset($param['database']) ) $this->database = $param['database'];
        if ( isset($param['dsn']) ) $this->dsn = $param['dsn'];
        $this->connect();
    }

    abstract function connect();
    abstract function assembleQuery();
        
    function disconnect()
    {
        $this->dbh = null;
    }
    
    function &PDO() {
        return $this->dbh;
    }

    function __invoke() {
        return $this->dbh;
    }
    
    function getdate($timestamp = false) {
        $date = $this->dbh->query("SELECT CURRENT_TIMESTAMP")->fetch(\PDO::FETCH_NUM)[0];
        if ($timestamp) return strtotime($date);
        return date("Y-m-d H:i:s",strtotime($date));
        
        //static $diff = null;
        //if ($diff === null) {
        //    $date = $this->dbh->query("SELECT CURRENT_TIMESTAMP")->fetch(\PDO::FETCH_NUM)[0];
        //    $diff = time() - strtotime($date);
        //    //debug(strtotime($date)."=".(time()-$diff));
        //}
        //return date("Y-m-d H:i:s",time()-$diff);
    }
    
    function fetchRecords($stmt, $primarykey = false) {
        $rows = $stmt->fetchAll(PDO::FETCH_CLASS,'\Oz\Database\Record');
        $collection = new Records;
        foreach ($rows as $key => $row) {
            if ($primarykey !== false && isset($row->$primarykey) ) {
                $key = $row->$primarykey;
            }
            $collection->$key = $row;
        }
        return $collection;
    }

    function query($query, $args = [], $quick = false, $fetch_mode = \PDO::FETCH_BOTH)
        {
            $this->resetQuery();
            $query = trim($query);
            $this->getSQL = $query;
            $this->bindValues = $args;
            debug([$this->getSQL,$this->bindValues],255);
            if ($quick == true) {
                    $stmt = $this->dbh->prepare($query);
                    $stmt->execute($this->bindValues);
                    $this->rowCount = $stmt->rowCount();
                    return $stmt->fetchAll($fetch_mode);
            }else{
                if (strpos( strtoupper($query), "SELECT" ) === 0 ) {
                    $stmt = $this->dbh->prepare($query);
                    $stmt->execute($this->bindValues);
                    $this->rowCount = $stmt->rowCount();
                    return $this->fetchRecords($stmt);
//                    
//                    
//                    $rows = $stmt->fetchAll(PDO::FETCH_CLASS,'\Oz\Database\Record');
//                    //$collection= [];
//                    $collection = new Records;
//                    //$x=0;
//                    foreach ($rows as $key => $row) {
//                            $collection->offsetSet($key,$row);
//                    }
//
//                    return $collection;

                }else{
                    $this->getSQL = $query;
                    $stmt = $this->dbh->prepare($query);
                    $stmt->execute($this->bindValues);
                    return $stmt->rowCount();
                }
            }
        }

        function exec()
        {
                //assimble query
                        $this->sql .= $this->where;
                        $this->getSQL = $this->sql;
                        $stmt = $this->dbh->prepare($this->sql);
                        $stmt->execute($this->bindValues);
                        return $stmt->rowCount();
        }

        abstract function describe($table);

        function resetQuery()
        {
                //$this->table = null;
                $this->columns = null;
                $this->sql = null;
                $this->bindValues = null;
                $this->limit = null;
                $this->orderBy = null;
                $this->getSQL = null;
                $this->where = null;
                $this->orWhere = null;
                $this->whereCount = 0;
                $this->isOrWhere = false;
                $this->rowCount = 0;
                $this->lastIDInserted = 0;
                return $this;
        }

        public function delete($table_name, $id=null)
        {
                $this->resetQuery();

                $this->sql = "DELETE FROM {$table_name}";

                if (isset($id)) {
                        // if there is an ID
                        if (is_numeric($id)) {
                                $this->sql .= " WHERE {$this->primary_key} = ?";
                                $this->bindValues[] = $id;
                        // if there is an Array
                        }elseif (is_array($id)) {
                                $arr = $id;
                                $count_arr = count($arr);
                                $x = 0;

                                foreach ($arr as  $param) {
                                        if ($x == 0) {
                                                $this->where .= " WHERE ";
                                                $x++;
                                        }else{
                                                if ($this->isOrWhere) {
                                                        $this->where .= " Or ";
                                                }else{
                                                        $this->where .= " AND ";
                                                }

                                                $x++;
                                        }
                                        $count_param = count($param);

                                        if ($count_param == 1) {
						$this->where .= "{$this->primary_key} = ?";
						$this->bindValues[] =  $param[0];
                                        }elseif ($count_param == 2) {
                                                $operators = explode(',', "=,>,<,>=,>=,<>");
                                                $operatorFound = false;

                                                foreach ($operators as $operator) {
                                                        if ( strpos($param[0], $operator) !== false ) {
                                                                $operatorFound = true;
                                                                break;
                                                        }
                                                }

                                                if ($operatorFound) {
                                                        $this->where .= $param[0]." ?";
                                                }else{
                                                        $this->where .= " ".trim($param[0])." = ?";
                                                }

                                                $this->bindValues[] =  $param[1];
                                        }elseif ($count_param == 3) {
                                                $this->where .= " ".trim($param[0]). "  ". $param[1]. " ?";
                                                $this->bindValues[] =  $param[2];
                                        }

                                }
                                //end foreach
                        }
                        // end if there is an Array
                        $this->sql .= $this->where;

                        $this->getSQL = $this->sql;
                        $stmt = $this->dbh->prepare($this->sql);
                        $stmt->execute($this->bindValues);
                        debug($this->sql,10);
                        debug($this->bindValues,10);
                        return $stmt->rowCount();
                }// end if there is an ID or Array
                // $this->getSQL = "<b>Attention:</b> This Query will update all rows in the table, luckily it didn't execute yet!, use exec() method to execute the following query :<br>". $this->sql;
                // $this->getSQL = $this->sql;
                return $this;
        }

	public function update($table_name, $fields = [], $id=null)
	{
		$this->resetQuery();
		$set ='';
		$x = 1;

		foreach ($fields as $column => $field) {
			$set .= "$column = ?";
			$this->bindValues[] = $field;
			if ( $x < count($fields) ) {
				$set .= ", ";
			}
			$x++;
		}

		$this->sql = "UPDATE {$table_name} SET $set";
		
		if (isset($id)) {
			// if there is an ID
			if (is_numeric($id)) {
				$this->sql .= " WHERE {$this->primary_key} = '$id'";
				//$this->bindValues[] = $id;
			// if there is an Array
			}elseif (is_array($id)) {
				$arr = $id;
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$this->where .= " WHERE ";
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);

					if ($count_param == 1) {
						$this->where .= "{$this->primary_key} = '$param[0]'";
						//$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= " ".trim($param[0])." = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= " ".trim($param[0]). " ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}

				}
				//end foreach
			}
			// end if there is an Array
			$this->sql .= $this->where;
			$this->getSQL = $this->sql;
			$stmt = $this->dbh->prepare($this->sql);
			$stmt->execute($this->bindValues);
                        debug($this->sql,10);
                        debug($this->bindValues,10);
			return $stmt->rowCount();
		}// end if there is an ID or Array
		// $this->getSQL = "<b>Attention:</b> This Query will update all rows in the table, luckily it didn't execute yet!, use exec() method to execute the following query :<br>". $this->sql;
		// $this->getSQL = $this->sql;
		return $this;
	}

	public function insert( $table_name, $fields = [] )
	{
		$this->resetQuery();

		$keys = implode(', ', array_keys($fields));
		$values = '';
		$x=1;
		foreach ($fields as $field => $value) {
			$values .='?';
			$this->bindValues[] =  $value;
			if ($x < count($fields)) {
				$values .=', ';
			}
			$x++;
		}
 
		$this->sql = "INSERT INTO {$table_name} ({$keys}) VALUES ({$values})";
		$this->getSQL = $this->sql;
		$stmt = $this->dbh->prepare($this->sql);
		$stmt->execute($this->bindValues);
		$this->lastIDInserted = $this->dbh->lastInsertId();
                debug($this->sql,10);
                debug($this->bindValues,10);
		return $this->lastIDInserted;
	}//End insert function

        public function lastId()
        {
                return $this->lastIDInserted;
        }

        public function table($table_name = null)
        {
            if (empty($table_name)) {
               return $this->table; 
            }
            $this->resetQuery();
            $this->table = $table_name;
            return $this;
        }

	public function select($columns)
	{
            if (!is_array($columns)) {
		$columns = explode(',', $columns);
            }
            $cols = '';
            $comma = '';
            foreach ($columns as $key => $column) {
                $column = trim($column);
                if (is_numeric($key)) {
                    if ($column == '*') {
                        $cols .= $comma.$column;
                    } else {
                        $cols .= $comma.''.trim($column).'';
                    }
                } else {
                   $cols .= $comma.trim($key).' as '.$column;
                }
                if (empty($comma)) {$comma = ',';}
            }
            $this->columns = $cols;
            return $this;
	}

	public function where()
	{
		if ($this->whereCount == 0) {
			$this->where .= " WHERE ";
			$this->whereCount+=1;
		}else{
			$this->where .= " AND ";
		}

		$this->isOrWhere= false;

		// call_user_method_array('where_orWhere', $this, func_get_args());
		//Call to undefined function call_user_method_array()
		//echo print_r(func_num_args());
		$num_args = func_num_args();
		$args = func_get_args();
		if ($num_args == 1) {
			if (is_numeric($args[0])) {
				$this->where .= "{$this->primary_key} = ?";
				$this->bindValues[] =  $args[0];
                        }elseif (is_string($args[0])) {
				$this->where .= $args[0];
			}elseif (is_array($args[0])) {
				$arr = $args[0];
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);
					if ($count_param == 1) {
						$this->where .= "{$this->primary_key} = ?";
						$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= " ".trim($param[0])." = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= " ".trim($param[0]). " ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}
				}
			}
			// end of is array
		}elseif ($num_args == 2) {
			$operators = explode(',', "=,>,<,>=,>=,<>,like,LIKE");
			$operatorFound = false;
			foreach ($operators as $operator) {
				if ( strpos($args[0], $operator) !== false ) {
					$operatorFound = true;
					break;
				}
			}

			if ($operatorFound) {
				$this->where .= $args[0]." ?";
			}else{
				$this->where .= " ".trim($args[0])."  = ?";
			}

			$this->bindValues[] =  $args[1];

		}elseif ($num_args == 3) {
			
			$this->where .= " ".trim($args[0]). " ". $args[1]. " ?";
			$this->bindValues[] =  $args[2];
		}

		return $this;
	}

	public function orWhere()
	{
		if ($this->whereCount == 0) {
			$this->where .= " WHERE ";
			$this->whereCount+=1;
		}else{
			$this->where .= " OR ";
		}
		$this->isOrWhere= true;
		// call_user_method_array ( 'where_orWhere' , $this ,  func_get_args() );

		$num_args = func_num_args();
		$args = func_get_args();
		if ($num_args == 1) {
			if (is_numeric($args[0])) {
				$this->where .= " {$this->primary_key} = ?";
				$this->bindValues[] =  $args[0];
                        }elseif (is_string($args[0])) {
				$this->where .= $args[0];
			}elseif (is_array($args[0])) {
				$arr = $args[0];
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);
					if ($count_param == 1) {
						$this->where .= " {$this->primary_key} = ?";
						$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= " ".trim($param[0])." = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= " ".trim($param[0]). " ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}
				}
			}
			// end of is array
		}elseif ($num_args == 2) {
			$operators = explode(',', "=,>,<,>=,>=,<>,like,LIKE");
			$operatorFound = false;
			foreach ($operators as $operator) {
				if ( strpos($args[0], $operator) !== false ) {
					$operatorFound = true;
					break;
				}
			}

			if ($operatorFound) {
				$this->where .= $args[0]." ?";
			}else{
				$this->where .= " ".trim($args[0])." = ?";
			}

			$this->bindValues[] =  $args[1];

		}elseif ($num_args == 3) {
			
			$this->where .= " ".trim($args[0]). "  ". $args[1]. " ?";
			$this->bindValues[] =  $args[2];
		}

		return $this;
	}


        public function &get()
        {
                $this->assembleQuery();
                $this->getSQL = $this->sql;

                $stmt = $this->dbh->prepare($this->sql);
                $stmt->execute($this->bindValues);
                $this->rowCount = $stmt->rowCount();
                $rows = $this->fetchRecords($stmt,$this->primary_key ?? false);
                return $rows;
//                $rows = $stmt->fetchAll(PDO::FETCH_CLASS, '\Oz\Database\Record');
//                //$collection= [];
//                $collection = new Records;
//                $x=0;
//                foreach ($rows as $key => $row) {
//                        $collection->offsetSet($x++,$row);
//                }
//                return $collection;
        }
        
        public function first() {
            return $this->get()->first();
        }
        // Quick get
        public function QGet()
        {
                $this->assembleQuery();
                $this->getSQL = $this->sql;
                $stmt = $this->dbh->prepare($this->sql);
                $stmt->execute($this->bindValues);
                $this->rowCount = $stmt->rowCount();
                return $stmt->fetchAll();
        }


 
//	function assimbleQuery()
//	{
//		if ( $this->columns !== null ) {
//			$select = $this->columns;
//		}else{
//			$select = " * ";
//		}
//                
//		$this->sql = "SELECT ".$this->limit.$select." FROM  ".$this->table." ";
//
//		if ($this->where !== null) {
//			$this->sql .= $this->where;
//		}
//
//		if ($this->orderBy !== null) {
//			$this->sql .= $this->orderBy;
//		}
//	}

        public function limit($limit, $offset=null)
        {
                if ($offset ==null ) {
                        $this->limit = " LIMIT {$limit}";
                }else{
                        $this->limit = " LIMIT {$limit} OFFSET {$offset}";
                }

                return $this;
        }

        /**
         * Sort result in a particular order according to a column name
         * @param  string $field_name The column name which you want to order the result according to.
         * @param  string $order      it determins in which order you wanna view your results whether 'ASC' or 'DESC'.
         * @return object             it returns DB object
         */
        public function orderBy($field_name, $order = 'ASC')
        {
                $field_name = trim($field_name);

                $order =  trim(strtoupper($order));

                // validate it's not empty and have a proper valuse
                if ($field_name !== null && ($order == 'ASC' || $order == 'DESC')) {
                        if ($this->orderBy ==null ) {
                                $this->orderBy = " ORDER BY $field_name $order";
                        }else{
                                $this->orderBy .= ", $field_name $order";
                        }

                }

                return $this;
        }

        public function paginate($page, $limit, $sql = false)
        {
                // Start assimble Query
                if (func_num_args() == 3) {
                    $stmt = $this->dbh->prepare("select count(*) from ( $sql ) c");
                    //$totalRows = $this->dbh->query($sql)->rowCount();
                } else {
                    $countSQL = "SELECT COUNT(*) FROM `$this->table`";
                    if ($this->where !== null) {
                            $countSQL .= $this->where;
                    }
                    $stmt = $this->dbh->prepare($countSQL);
                }
                $stmt->execute($this->bindValues);
                $totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];

                $offset = ($page-1)*$limit;
                $this->pagination['currentPage'] = $page;
                $this->pagination['lastPage'] = ceil($totalRows/$limit);
                $this->pagination['nextPage'] = $page + 1;
                $this->pagination['previousPage'] = $page-1;
                $this->pagination['totalRows'] = $totalRows;
                // if last page = current page
                if ($this->pagination['lastPage'] ==  $page) {
                        $this->pagination['nextPage'] = null;
                }
                if ($page == 1) {
                        $this->pagination['previousPage'] = null;
                }
                if ($page > $this->pagination['lastPage']) {
                        return [];
                }

                if (func_num_args() == 3) {
                    $sql = func_get_arg(2);
                } else {
                    $this->assembleQuery();
                    $sql = $this->sql;
                }
                $this->getSQL = $sql . " LIMIT {$limit} OFFSET {$offset}";
                debug($this->getSQL,10);
                $stmt = $this->dbh->prepare($this->getSQL);
                $stmt->execute($this->bindValues);
                $this->rowCount = $stmt->rowCount();
                
                
                
                
                return $this->fetchRecords($stmt);
        }

        public function count()
        {
                // Start assimble Query
                $countSQL = "SELECT COUNT(*) FROM $this->table";

                if ($this->where !== null) {
                        $countSQL .= $this->where;
                }

                if ($this->limit !== null) {
                        $countSQL .= $this->limit;
                }
                // End assimble Query

                $stmt = $this->dbh->prepare($countSQL);
                $stmt->execute($this->bindValues);

                $this->getSQL = $countSQL;

                return $stmt->fetch(PDO::FETCH_NUM)[0];
        }


        public function QPaginate($page, $limit)
        {
                // Start assimble Query
                $countSQL = "SELECT COUNT(*) FROM $this->table";
                if ($this->where !== null) {
                        $countSQL .= $this->where;
                }
                // Start assimble Query

                $stmt = $this->dbh->prepare($countSQL);
                $stmt->execute($this->bindValues);
                $totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
                // echo $totalRows;

                $offset = ($page-1)*$limit;
                // Refresh Pagination Array
                $this->pagination['currentPage'] = $page;
                $this->pagination['lastPage'] = ceil($totalRows/$limit);
                $this->pagination['nextPage'] = $page + 1;
                $this->pagination['previousPage'] = $page-1;
                $this->pagination['totalRows'] = $totalRows;
                // if last page = current page
                if ($this->pagination['lastPage'] ==  $page) {
                        $this->pagination['nextPage'] = null;
                }
                if ($page == 1) {
                        $this->pagination['previousPage'] = null;
                }
                if ($page > $this->pagination['lastPage']) {
                        return [];
                }

                $this->assembleQuery();

                $sql = $this->sql . " LIMIT {$limit} OFFSET {$offset}";
                $this->getSQL = $sql;

                $stmt = $this->dbh->prepare($sql);
                $stmt->execute($this->bindValues);
                $this->rowCount = $stmt->rowCount();

                return $stmt->fetchAll();
	}

	public function PaginationInfo()
	{
		return $this->pagination;
	}

	public function getSQL()
	{
		return $this->getSQL;
	}
        
        public function getBindParams() {
            return $this->bindValues;
        }

	public function getCount()
	{
		return $this->rowCount;
	}

	public function rowCount()
	{
		return $this->rowCount;
	}


}

