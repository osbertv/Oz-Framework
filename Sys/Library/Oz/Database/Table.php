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

namespace Oz\Database;

class Table extends \Oz\Database\Connection {

    private $table;
    public static $fields = [];
    public  $active_record = [];
    public  $primary_key = null;
    public  $fix_timestamps = true;
    public  $timestamp_fields_created = ['date_created','created_at','datecreated','createddate','create_date','created_date','created','timestamp'];
    public  $timestamp_fields_modified = ['date_updated','updated_at','date_modified','modifieddate','modified_date','dateupdated','updateddate','updated_date','updated'];
    
    public function __construct($table = null, $param = null) {
        if (is_array($param)) {
            parent::__construct($param);
        } else {
            parent::__construct();
        }
        if (is_string($table) && !is_numeric($table)) {
            $this->table($table);
        } elseif (!is_numeric($table)) {
            $table = strtolower(substr(strrchr(get_called_class(), "\\"), 1));
            if (substr($table,-1,1) != 's') {$table .= 's';}
            $this->table($table);
        } 
        
        if (empty(Table::$fields[$this->table_name()])) {
            Table::$fields[$this->table_name()] = $this->handle()->describe($this->table);
        }
        
        if (empty($this->primary_key)) {
            foreach ($this->fields() as $fld) {
                if ($fld['is_key']) { 
                    $this->primary_key ($fld['name']);
                    break;
                }
            }
        } else {
            $this->handle()->primary_key = $this->primary_key;
        }
        
        
        if (is_numeric($table)) {
            $this->find($table);
        } elseif (is_numeric($param)) {
            $this->find($param);
        }
    }
    
    function primary_key()
    {
        if (func_num_args()) {
            $this->primary_key = $this->handle()->primary_key = func_get_arg(0);
            return $this->handle()->primary_key;
        }
        return $this->handle()->primary_key;
    }

    private function fields_update_values() {
        $fields = Table::$fields[$this->table_name()] ?? null;
        foreach ($this->active_record as $key => $value) {
            $fields[$key]['value'] = $value;
        }
        return $fields;
    } 
    
    function table_name() {
        return $this->handle()->database.".".$this->table;
    }
    
    function fields() {
        if (func_num_args()) {
            $fld = func_get_arg(0);
            if ($fld=='*') {
                if (func_num_args() == 2)
                    return Table::$fields[$this->table_name()] = func_get_arg(1);
                return Table::$fields[$this->table_name()];
            }
            Table::$fields[$this->table_name()][$fld]['value'] = $this->active_record->$fld ?? null;
            return Table::$fields[$this->table_name()][$fld];
        }
        return $this->fields_update_values();
    }
    
    function find($id) {
        $this->resetQuery();
        return $this->active_record = $this->where($this->primary_key,$id)->get()->first();
    }
    
    function eof() {
        return empty($this->active_record->{$this->primary_key()});
    }
    
    function table($table = null){
        if (empty($table)) {
           return $this->table; 
        }
        $this->table = $this->config->prefix.$table;
        $this->handle()->table($this->table);
    }
    
    function insert($fields = [] ) {
        if ($this->fix_timestamps) $fields = $this->fixtimestamp($fields);
        debug($fields,10);
        return $this->handle()->insert($this->table(),$fields);
    }
    
    function delete($id) {
        return $this->handle()->delete($this->table,$id);
    }

    function update($fields,$id = null) {
        if (empty($id)) $id = $this->active_record->{$this->primary_key()};
        if ($this->fix_timestamps) $fields = $this->fixtimestamp($fields);
        debug($fields,10);
        return $this->handle()->update($this->table(),$fields,$id);
    }
    
    private function fixtimestamp($fields) {
        $date = date("Y-m-d H:i:s");
        $all_fields = $this->fields();
        foreach (array_keys($all_fields) as $k) {
            if (in_array(strtolower($k), $this->timestamp_fields_modified)) {
                $fields[$k] = $date;
                debug("fixtimestamp:$k=$date",255);
            }
            if (in_array(strtolower($k), $this->timestamp_fields_created)) {
                if (empty($fields[$k]) && empty($this->active_record->$k)) {
                    $fields[$k] = $date;
                    debug("fixtimestamp:$k=$date",255);
                }
            }
        }
//        foreach ($this->timestamp_fields_modified as $value) {
//            if (key_exists($value,$all_fields)) {
//                $fields[$value] = $date;
//            }
//        }
//        foreach ($this->timestamp_fields_created as $value) {
//            if (key_exists($value,$all_fields)) {
//                if (empty($fields[$value]) && empty($this->active_record->$value)) {
//                    $fields[$value] = $date;
//                }
//            }
//        }
        return $fields;
    }
    
    function save($fields = []) {
        if (empty($fields)) {
            $fields = (array) $this->active_record;
        } else {
            if (empty($this->active_record)) {
                $this->active_record = new \stdClass();
            }
            foreach( $fields as $k => $v) 
                $this->active_record->$k = $v;
        }
        if (isset($fields[$this->primary_key])) {
            $id = $fields[$this->primary_key];
            unset($fields[$this->primary_key]);
            debug($this->table()."->update($id)",10);
            return $this->update($fields,$id);
        }
        //debug($fields,10);
        return $this->insert($fields);
    }
    
    function validate_form($form, &$fields = []) {
        $validated = true;
        foreach ($this->fields() as $fld) {
            if ( (!isset($form[$fld['name']])) && $fld['required'] && blank($fld['value']??null) && (!$fld['auto_increment']) ) {
                //if ($form[$fld['name']] !== '0') {
                flash()->error($fld['name'],"{$fld['name']} is required.");
                flash()->alert($fld['name'],"{$fld['name']} is required.");
                $validated = false;
                //}
            } elseif (key_exists($fld['name'], $form)) {
                $fields[$fld['name']] = $form[$fld['name']];
            }
        } 
        return $validated;
    }
    
    function __invoke() {
        return (func_num_args()) 
            ? call_user_func_array(array($this,'__get'), func_get_args()) 
                : $this->active_record;
//        if (func_num_args()) {
//            return $this->__get(func_get_arg(0));
//        }
//        return $this->active_record;
    }

    public static function __callStatic($name, $arguments) {
        //static $classes = [];
        $class = get_called_class();
        //if (!isset($classes[$class]))
        //    $classes[$class] = new $class();
        $instance = $class::getInstance();
        return call_user_func_array(array($instance,$name), $arguments);
//        $class = get_called_class();
//        return call_user_func_array(array($class::getInstance(),$name), $arguments);
//        $args = $arguments;
//        switch (count($args)) {
//            case 0:
//                return $class::getInstance()->$name();
//            case 1:
//                return $class::getInstance()->$name($args[1]);
//            case 2:
//                return $class::getInstance()->$name($args[1],$args[2]);
//            case 3:
//                return $class::getInstance()->$name($args[1],$args[2],$args[3]);
//            case 4:
//                return $class::getInstance()->$name($args[1],$args[2],$args[3],$args[4]);
//            case 5:
//                return $class::getInstance()->$name($args[1],$args[2],$args[3],$args[4],$args[5]);
//            case 6:
//                return $class::getInstance()->$name($args[1],$args[2],$args[3],$args[4],$args[5],$args[6]);
//        }
//        return $class::getInstance()->$name($args);
    }
    
   
    function __get($name) {
        if (is_object($this->active_record)) {
            if (property_exists($this->active_record, $name)) {
                return $this->active_record->$name;
            }
        } elseif (is_array($this->active_record)) {
            if (key_exists($name,$this->active_record)) {
                return $this->active_record[$name];
            }
        }
    }

    function __set($name, $value) {
        if (is_array($this->active_record)) {
            return $this->active_record[$name] = $value;
        }
        if (!isset($this->active_record)) {
            $this->active_record = new \Oz\database\Record();
        }
        return $this->active_record->$name =  $value;
    }
    
    function paginationHtml($base_url,$show_search = false) {
        $info = $this->paginationInfo();
        $info['base_url'] = $base_url;
        $info['show_search'] = $show_search;
        return (string)view('helper/paginate', $info , false);
    }
    
    function __toString() {
        return (string)$this->active_record;
    }
    
    function toArray() {
        $rows = $this->get();
        $key = $this->primary_key();
        $arr = [];
        foreach ($rows as $row) {
            $arr[$row->$key] = (array) $row;
        }
        return $arr;
    }
    
}