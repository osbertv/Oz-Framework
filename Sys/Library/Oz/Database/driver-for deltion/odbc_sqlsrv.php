<?php

namespace OV\Database\driver;
use \PDO;
use \OV\Database\Records;

class odbc_sqlsrv extends Driver {

    private $driver	= "odbc";
    private $host	= "localhost";
    private $port	= "1433";
    private $username;
    private $password;
    private $database;
    private $dsn = 'mssql';

    public $primary_key = 'id';
    
    private $dbh = null, $table, $columns, $sql, $bindValues, $getSQL,
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

    public function connect()
    {
        if ($this->dbh) 
                return $this->dbh;
        $server = ($this->port == "1433") ? $this->host : $this->host.",".$this->port;
	//$dsn = "odbc:{FREETDS};HOSTNAME={$this->host};PORT={$this->port};DATABASE={$this->database}";
	$dsn = "odbc:{$this->dsn}";
        try {
            $this->dbh = new PDO($dsn, $this->username, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
        }
        $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->dbh->exec('use '.$this->database);
        return $this->dbh;
    }
        
    public function disconnect()
    {
        $this->dbh = null;
    }

        public function query($query, $args = [], $quick = false, $fetch_mode = \PDO::FETCH_BOTH)
	{
            $this->resetQuery();
            $query = trim($query);
            $this->getSQL = $query;
            $this->bindValues = $args;

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

                    $rows = $stmt->fetchAll(PDO::FETCH_CLASS,'\OV\Database\Record');
                    //$collection= [];
                    $collection = new Records;
                    $x=0;
                    foreach ($rows as $key => $row) {
                            $collection->offsetSet($x++,$row);
                    }

                    return $collection;

                }else{
                    $this->getSQL = $query;
                    $stmt = $this->dbh->prepare($query);
                    $stmt->execute($this->bindValues);
                    return $stmt->rowCount();
                }
            }
	}
        
	public function exec()
	{
		//assimble query
			$this->sql .= $this->where;
			$this->getSQL = $this->sql;
			$stmt = $this->dbh->prepare($this->sql);
			$stmt->execute($this->bindValues);
			return $stmt->rowCount();
	}
        
        public function describe($table) {
            $stmt = $this->dbh->prepare("exec sp_columns $table");$stmt->execute();
            $rs = $stmt->fetchAll(\PDO::FETCH_BOTH);
            $fields = [];
            foreach ($rs as $row) {
                $is_inc = (stripos($row['TYPE_NAME'], 'identity')!==false);
                $fields[$row['COLUMN_NAME']] = [
                        'name' => $row['COLUMN_NAME'],
                        'required' => ($row['NULLABLE'] == 'NO' && empty($row['COLUMN_DEF']) && !$is_inc),
                        'type' => $row['TYPE_NAME'],
                        'length' => $row['PRECISION'],
                        'visible' => true,
                        'is_key' => false
                    ];
            }
            $stmt->closeCursor();
            $stmt = $this->dbh->prepare("exec sp_pkeys $table");$stmt->execute();
            $rs = $stmt->fetchAll(\PDO::FETCH_BOTH);
            foreach ($rs as $row) {
                $fields[$row['COLUMN_NAME']]['is_key'] = true;
                $this->primary_key = $row['COLUMN_NAME'];
            }
            return $fields;
        }

	private function resetQuery()
	{
		$this->table = null;
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
	}

	public function delete($table_name, $id=null)
	{
		$this->resetQuery();

		$this->sql = "DELETE FROM [{$table_name}]";
		
		if (isset($id)) {
			// if there is an ID
			if (is_numeric($id)) {
				$this->sql .= " WHERE [{$this->primary_key}] = ?";
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
						$this->where .= "[{$this->primary_key}] = ?";
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
							$this->where .= "[".trim($param[0])."] = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "[".trim($param[0]). "] ". $param[1]. " ?";
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
			$set .= "[$column] = ?";
			$this->bindValues[] = $field;
			if ( $x < count($fields) ) {
				$set .= ", ";
			}
			$x++;
		}

		$this->sql = "UPDATE [{$table_name}] SET $set";
		
		if (isset($id)) {
			// if there is an ID
			if (is_numeric($id)) {
				$this->sql .= " WHERE [{$this->primary_key}] = $id";
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
						$this->where .= "[{$this->primary_key}] = $param[0]";
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
							$this->where .= "[".trim($param[0])."] = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "[".trim($param[0]). "] ". $param[1]. " ?";
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

		$keys = implode('], [', array_keys($fields));
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
 
		$this->sql = "INSERT INTO [{$table_name}] ([{$keys}]) VALUES ({$values})";
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

	public function table($table_name)
	{
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
                        $cols .= $comma.'['.trim($column).']';
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
				$this->where .= "[{$this->primary_key}] = ?";
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
						$this->where .= "[{$this->primary_key}] = ?";
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
							$this->where .= "[".trim($param[0])."] = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "[".trim($param[0]). "] ". $param[1]. " ?";
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
				$this->where .= "[".trim($args[0])."] = ?";
			}

			$this->bindValues[] =  $args[1];

		}elseif ($num_args == 3) {
			
			$this->where .= "[".trim($args[0]). "] ". $args[1]. " ?";
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
				$this->where .= "[{$this->primary_key}] = ?";
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
						$this->where .= "[{$this->primary_key}] = ?";
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
							$this->where .= "[".trim($param[0])."] = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "[".trim($param[0]). "] ". $param[1]. " ?";
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
				$this->where .= "[".trim($args[0])."] = ?";
			}

			$this->bindValues[] =  $args[1];

		}elseif ($num_args == 3) {
			
			$this->where .= "[".trim($args[0]). "] ". $args[1]. " ?";
			$this->bindValues[] =  $args[2];
		}

		return $this;
	}

	// private function where_orWhere()
	// {

	// }

	public function get()
	{
		$this->assimbleQuery();
		$this->getSQL = $this->sql;
                debug([$this->getSQL,$this->bindValues],10);
		$stmt = $this->dbh->prepare($this->sql);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();

		$rows = $stmt->fetchAll(PDO::FETCH_CLASS,'\OV\Database\Record');
		//$collection= [];
		$collection = new Records;
		$x=0;
		foreach ($rows as $key => $row) {
			$collection->offsetSet($x++,$row);
		}

		return $collection;
	}
	// Quick get
	public function QGet()
	{
		$this->assimbleQuery();
		$this->getSQL = $this->sql;

		$stmt = $this->dbh->prepare($this->sql);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();

		return $stmt->fetchAll();
	}


	private function assimbleQuery()
	{
		if ( $this->columns !== null ) {
			$select = $this->columns;
		}else{
			$select = " * ";
		}
                
		$this->sql = "SELECT ".$this->limit.$select." FROM [".$this->table."]";

		if ($this->where !== null) {
			$this->sql .= $this->where;
		}

		if ($this->orderBy !== null) {
			$this->sql .= $this->orderBy;
		}
	}

	private function prepareSQLforPaging($limit, $offset)
	{
		if ( $this->columns !== null ) {
			$select = $this->columns;
		}else{
			$select = "*";
		}

		if ($this->orderBy !== null) {
			$order_by = " ROW_NUMBER() OVER ({$this->orderBy}) as __row_number ";
		} else {
			$order_by = " ROW_NUMBER() OVER ( ORDER BY {$this->primary_key}) as __row_number ";
                }
                
		$sql = "SELECT ".$select.",$order_by FROM [".$this->table."] ";

		if ($this->where !== null) {
			$sql .= $this->where;
		}
                $offset2 = $offset + $limit; 
        	return $this->sql = "SELECT * FROM ($sql) as __paging_table WHERE __paging_table.__row_number >= $offset AND __paging_table.__row_number < $offset2";
	}

        public function limit($limit)
	{
            $this->limit = " TOP {$limit} ";
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

	public function paginate($page, $limit)
	{
		// Start assimble Query
		$countSQL = "SELECT COUNT(*) FROM [$this->table]";
		if ($this->where !== null) {
			$countSQL .= $this->where;
		}
		// Start assimble Query

		$stmt = $this->dbh->prepare($countSQL);
		$stmt->execute($this->bindValues);
		$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
		// echo $totalRows;
                //$page = (empty($page)) ? 1 : $page;
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
		$this->getSQL = $this->prepareSQLforPaging($limit, $offset);
                debug($this->getSQL,10);
		$stmt = $this->dbh->prepare($this->getSQL);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();


		$rows = $stmt->fetchAll(PDO::FETCH_CLASS,'OV\Database\Record');
		//$collection = [];
		$collection = new Records;
		$x=0;
		foreach ($rows as $key => $row) {
			$collection->offsetSet($x++,$row);
		}

		return $collection;
	}

	public function count()
	{
		// Start assimble Query
		$countSQL = "SELECT COUNT(*) FROM [{$this->table}]";

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
		$countSQL = "SELECT COUNT(*) FROM [{$this->table}]";
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

		$this->assimbleQuery();

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

	public function getCount()
	{
		return $this->rowCount;
	}

	public function rowCount()
	{
		return $this->rowCount;
	}


}

