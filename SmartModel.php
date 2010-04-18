<?php

/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */

require_once dirname(__FILE__).'/Model.php';
require_once dirname(__FILE__).'/Validation.php';

class SmartModel extends Model {

	const debug = False;
	public function __construct(){
		parent::__construct();
	}
	public function relate(){
		$args = func_get_args();
		$join = array();
		$class_name = get_class($this);
		foreach($args as $model){
			if(is_object($model)){
				$class_name_r = get_class($model);
				$fk_r = $model->_fk;
				foreach($fk_r as $col_r => $table){
					if(strpos($table,'.') !== false){
						$m = substr($table,0,strpos($table,'.'));
						$v = substr($table,strpos($table,'.')+1);
						if($m == $class_name){
							$join[$v] = $class_name_r.'.'.$col_r;
						}
					}
				}
			}
		}

		$data =  $this->join($join);
		return $data;

	}
	public function reset(){
		if($this->is_assoc($this->_fields)){
			foreach($this->_fields as $k =>$v){
				unset($this->$k);
				unset($this->$v);
			}
		}else{
			foreach($this->_fields as $k){
				unset($this->$k);
			}
		}
	}
	public function smartRead($opt){
		$select = isset($opt['select']) ? $opt['select']: null;
		$where = isset($opt['where']) ? $opt['where']: null;
		$orderby = isset($opt['order']) ? $opt['order']: null;
		$limit = isset($opt['limit']) ? $opt['limit']: null;
		return parent::read($select,$where,$orderby,$limit);
	}
	public function smartUpdate($opt){
		$where = isset($opt['where']) ? $opt['where']: null;
		$update = isset($opt['update']) ? $opt['update']: null;;
		$options = isset($opt['options']) ? $opt['options']: null;;
		return parent::update($update,$where,$options);
	}

	public function smartInsert(){

	}

	public function smartAssign($array = null,&$obj = null){
		if(self::debug){
			echo "*************Starting Smart Assign Of Model*************<br>";
		}
		if($obj != null){
			$array_check = $obj->getKeys($obj->_fields);
			$class = get_class($obj);
			if(isset($array)){
				foreach($array as $k => $v){
					if(strpos($k,'.') !== false){
						$table = substr($k,0,strpos($k,'.'));
						$var = substr($k,strpos($k,'.')+1);
						if($table == $class){
							$colname = $obj->getFieldName($var);
							$obj->$colname = $v;
						}
					}
				}
			}
			if(self::debug){
				echo $this;
			}
		}else{
			$array_check = $this->getKeys($this->_fields);
			if(isset($array)){
				foreach($array as $k => $v){
					if(in_array($k,$array_check)){
						$this->$k = $v;
					}
					else {
						$colname = $this->getFieldName($k);
						$this->$colname = $v;
					}
				}
			}else{
				if(isset($_REQUEST)){
					foreach($array_check as $k){
						if(isset($_REQUEST[$k])){
							$this->$k = $_REQUEST[$k];
						}
					}
				}
			}
			if(self::debug){
				echo $this;
			}
		}

	}
	private $error_msg = "";
	public function validate(){
		$obj = get_object_vars($this);
		foreach($obj as $k=>$v){
			if(isset($this->_rules[$k])){
				if(is_array($this->_rules[$k])){
					$array_name = isset($this->_rules[$k]['array']) ? $this->_rules[$k]['array'] : null;
					$rule = isset($this->_rules[$k]['rule']) ? $this->_rules[$k]['rule'] : Validation::TYPE_ALLVALID;
					$msgs = isset($this->_rules[$k]['message']) ? $this->_rules[$k]['message'] : null;
					if(!isset($array_name))
					$array = isset($this->$array_name) ? $this->$array_name : null;
					else
					$array = $array_name;

					$msg = Validation::validate($this->_rules[$k]['rule'],$v,$array,$msgs);
				}else{
					$msg = Validation::validate($this->_rules[$k],$v);
				}
				if(!empty($msg)){
					$column = $this->getColumnName($k);
					$this->error_msg .= $column ." :  " .  $msg . "<br>";
				}
			}
		}
		if(!empty($this->error_msg)){
			return false;
		}else{
			return true;
		}
	}
	public function getValidationError(){
		return $this->error_msg;
	}
	public function __call($name,$args) {
		if(strpos($name,'get') === 0){
			$name = str_replace('get','',$name);
			$obj = get_object_vars($this);
			foreach($obj as $k=>$v){
				if($k == $name){
					return $v;
				}
			}
			foreach($obj as $k=>$v){
				if(strtolower($k) == strtolower($name)){
					return $v;
				}
			}
		}
		else if(strpos($name,'set') === 0){
			$name = str_replace('set','',$name);
			$obj = get_object_vars($this);
			foreach($obj as $k=>$v){
				if($k == $name){
					$this->$k = $args[0];
				}
			}
			foreach($obj as $k=>$v){
				if(strcasecmp($k,$name) ==0){
					$this->$k = $args[0];
				}
			}
		}
		return "";
	}
	public static function convertDotToUnderScore($data){
		$newdata = array();
		foreach($data as $row){
			$newrow = array();
			foreach($row as $key => $val){
				$key1 = str_replace(".","_",$key);
				$newrow[$key1] = $val;
			}
			$newdata[] = $newrow;
		}
		return $newdata;
	}
	public function __toString() {
		$string = "";
		$obj = get_object_vars($this);
		foreach($obj as $k=>$v){
			if(isset($this->_fields[$k])){
				$string .= $k . " : " . $v . "<br>";
			}else{
				if(in_array($k,$this->_fields))
				$string .= $k . " : " . $v . "<br>";
			}
		}
		return $string;
	}
}