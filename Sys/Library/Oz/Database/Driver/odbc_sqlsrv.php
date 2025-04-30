<?php

namespace Oz\Database\Driver;
use \PDO;
use \Oz\Database\Records;

class odbc_sqlsrv {
    use Driver;
    

    public function connect()
    {
        if ($this->dbh) 
                return $this->dbh;
        
	$dsn = "odbc:{$this->dsn}";
        try {
            $this->dbh = new PDO($dsn, $this->username, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->dbh->exec('use '.$this->database);
        } catch (PDOException $e) {
            debug($e->getMessage());
        }
        return $this->dbh;
    }
        
    function describe($table) {
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


	private function prepareSQLforPaging($limit, $offset, $sql = false)
	{
                if (!$sql) {
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
                }
                
                $offset2 = $offset + $limit; 
        	return $this->sql = "SELECT * FROM ($sql) as __paging_table WHERE __paging_table.__row_number >= $offset AND __paging_table.__row_number < $offset2";
	}

	public function assembleQuery()
	{
		if ( $this->columns !== null ) {
			$select = $this->columns;
		}else{
			$select = "*";
		}
                
                $limit = "";
		if ($this->limit !== null) {
			$limit = $this->limit;
		}

                $this->sql = "SELECT ".$limit.$select." FROM [".$this->table."] ";

		if ($this->where !== null) {
			$this->sql .= $this->where;
		}

		if ($this->orderBy !== null) {
			$this->sql .= $this->orderBy;
		}

	}

        public function limit($limit)
	{
            $this->limit = " TOP {$limit} ";
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
		$this->getSQL = $this->prepareSQLforPaging($limit, $offset, (func_num_args()==3) ? func_get_arg(2) : false);
                debug($this->getSQL,10);
		$stmt = $this->dbh->prepare($this->getSQL);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();
                return $this->fetchRecords($stmt, $this->primary_key ?? false);
//
//		$rows = $stmt->fetchAll(PDO::FETCH_CLASS,'Oz\Database\Record');
//		//$collection = [];
//		$collection = new Records;
//		$x=0;
//		foreach ($rows as $key => $row) {
//			$collection->offsetSet($x++,$row);
//		}
//
//		return $collection;
	}

	public function QPaginate($page, $limit)
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

		return $stmt->fetchAll();
	}


}

