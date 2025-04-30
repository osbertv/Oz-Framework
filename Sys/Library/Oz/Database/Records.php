<?php
namespace Oz\Database;

class Records implements \ArrayAccess{
    
   
       private function next_offset() {
           $offset = $this->offset();
           return $this->offset(++$offset);
       }
       
       private function offset() {
           static $offset = 0;
           if (func_num_args()) {
               return $offset = func_get_arg(0);
           }
           return $offset;
       }
    
       private function next($offset = false) {
           static $this_array = null;
           if (empty($this_array)) {
               $this_array = $this->toArray();
           }
           if ($offset === false) {
                $offset = $this->next_offset();
           }
           if ($offset == -1) {
                $offset = count($this_array)-1;
           }
           return ( isset($this_array[$offset]) ) ? $this_array[$offset] : null;
       }
       
       #[\ReturnTypeWillChange]
       public function offsetSet($offset, $value) {
           $this->$offset = $value;
       }

       public function toJSON()
       {
           return json_encode($this->toArray(), JSON_INVALID_UTF8_IGNORE | JSON_NUMERIC_CHECK);
       }

       public function toArray($use_key = false)
       {
           $array = [];
           if ($use_key) {
                foreach ($this as $item) {
                  $array[$item->{$use_key}] = $item;
                }
           } else {
                foreach ($this as  $item) {
                  $array[] = $item;
                }
           }
            return $array;
       }

       public function list($field)
       {
            $list = [];
            foreach ($this as  $item) {
              $list[] = $item->{$field};
            }
            return $list;
       }

       public function first()
       {
           foreach ($this as $item) {
               return $item;
           }
       }

       public function last()
       {
           return $this->next(-1);
       }

	   #[\ReturnTypeWillChange]
       public function offsetExists($offset) {
           return isset($this->$offset);
       }

	   #[\ReturnTypeWillChange]
       public function offsetUnset($offset) {
           unset($this->$offset);
       }

	   #[\ReturnTypeWillChange]
       public function offsetGet($offset) {
           return isset($this->$offset) ? $this->$offset : null;
       }

        public function item($key) {
            return isset($this->$key) ? $this->$key : null;
        }
        
        public function eof() {
            return $this->next($this->offset());
        }
        
        public function __get($name) {
            return (isset($this->{$this->offset}->$name)) ? $this->{$this->offset}->$name : false;
        }
        
        //public function next() {
        //    $this->offset = $this->offset+1;
        //}

        //public function previous() {
        //    $this->offset = $this->offset-1;
        //}

        public function __toString() {
            header("Content-Type: application/json;charset=utf-8");
            // return json_encode(get_object_vars($this));
            return  $this->toJSON();

        }

}
