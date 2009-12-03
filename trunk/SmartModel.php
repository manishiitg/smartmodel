<?php

/**
 * Author: Manish
 * Version: v0.2.6 alpha
 */
class SmartModel extends Model {

	const debug = false;
	public function __construct(){

	}
	public function reset(){
		foreach($this->_fields as $k =>$v){
			unset($this->$k);
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

	public function smartAssign($array = null){
		if(self::debug){
			echo "*************Starting Smart Assign Of Model*************<br>";
		}
		$obj = get_object_vars($this);
		if(isset($array)){
			foreach($array as $k => $v){
				if(isset($this->$k)){
					$this->$k = $v;
				}
				else {
					if($this->is_assoc($this->_fields)){
						foreach($this->_fields as $key => $value ){
							if(!empty($value)){
								if($k == $value){
									$this->$key = $v;
									break;
								}
							}else{
								if($k == $key){
									$this->$key = $v;
									break;
								}
							}
						}
					}else{
						foreach($this->_fields as $value ){
							if($k == $value){
								$this->$value = $v;
								break;
							}
						}
					}
				}
			}
		}else{
			if(isset($_REQUEST)){
				foreach($obj as $k=>$v){
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
					$db = $this->is_assoc($this->_fields) ? $this->_fields[$k] : $k;
					$db = empty($db) ? $k : $db;
					$this->error_msg .= $db ." :  " .  $msg . "<br>";
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
	public function __toString() {
		$string = "";
		$obj = get_object_vars($this);
		foreach($obj as $k=>$v){
			if($this->is_assoc($this->_fields)){
				if(isset($this->_fields[$k])){
					$db = empty($this->_fields[$k]) ? $k : $this->_fields[$k];
					$string .= $db . " : " . $v . "<br>";
				}
			}else{
				if(in_array($k,$this->_fields))
				$string .= $k . " : " . $v . "<br>";
			}
		}
		return $string;
	}
}