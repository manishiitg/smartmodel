<?php
/**
 * Author: Manish
 * Version: v0.2.6 alpha
 */
class Validation {
	const TYPE_ALLVALID = 0;

	const TYPE_NOT_EMPTY = 1;

	const TYPE_ISINTEGER = 2;
	const TYPE_ISSTRING = 3;
	const TYPE_ISBOOLEAN = 4;
	const TYPE_ISURL = 5;
	const TYPE_ISDECIMAL = 6;

	const TYPE_INARRAY = 8;

	const TYPE_ISEMAIL = 9;

	const TYPE_ISVISA = 10;
	const TYPE_ISMASTERCARD = 11;
	const TYPE_ISAMERICANEXPRESS = 12;
	const TYPE_ISDISCOVERCARD = 13;

	const TYPE_INMODEL = 14;
	const TYPE_NOTINMODEL = 15;

	const debug = false;
	public static function validate($type,$value,$array = null,$msg = null){
		if(self::debug){
			echo "*************Starting Validation Of Model*************<br>";
			echo "Value: $value<br>";
			echo "Array: $array <br>";
			echo "Message: $msg <br>";
		}
		$match = false;
		switch ($type){
			case self::TYPE_ALLVALID:
				if(self::debug){
					echo "Rule: ALL VALID <br>";

				}
				$match = true;
				break;
			case self::TYPE_NOT_EMPTY:
				if(self::debug){
					echo "Rule: NOT EMPTY <br>";
				}
				if(!empty($value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Should Not Be Empty";
					}
				}
				break;
			case self::TYPE_ISINTEGER:
				if(self::debug){
					echo "Rule: IS INTEGER <br>";
				}
				$pattern = "/^[0-9]*$/";
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Should Be An Integer";
					}
				}
				break;

			case self::TYPE_ISSTRING:
				if(self::debug){
					echo "Rule: IS STRING <br>";
				}
				if(is_string($value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Should Be A String";
					}
				}
				break;
			case self::TYPE_ISBOOLEAN:
				if(self::debug){
					echo "Rule: IS BOOLEAN <br>";
				}
				if(is_bool($value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Should Be A Boolean";
					}
				}
				break;
			case self::TYPE_ISURL:
				if(self::debug){
					echo "Rule: IS URL <br>";
				}
				$pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Should Be A Valid URL";
					}
				}
				break;
			case self::TYPE_ISDECIMAL:
				if(self::debug){
					echo "Rule: IS NUMBER <br>";
				}
				$pattern = '/^[0-9]*\.?[0-9]*$/';
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Should Be A Number";
					}
				}
				break;
			case self::TYPE_INARRAY:
				if(self::debug){
					echo "Rule: IN ARRAY <br>";
				}
				if(isset($array)){
					if(in_array($value,array_keys($array))){
						$match = true;
					}else{
						if(!isset($msg)){
							$msg = "Should Be In " . implode(",",$array);
						}
					}
				}else{
					$match = true;
				}
				break;
			case self::TYPE_ISEMAIL:
				$pattern = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/";
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Invalid EMail Address";
					}
				}
				break;
			case self::TYPE_ISVISA:
				$pattern = "/^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/i";
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Invalid Visa Card Address";
					}
				}
				break;
			case self::TYPE_ISMASTERCARD:
				$pattern = "/^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/i";
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Invalid Visa Card Address";
					}
				}
				break;
			case self::TYPE_ISAMERICANEXPRESS:
				$pattern = "/^3\d{3}[ \-]?\d{6}[ \-]?\d{5}$/i";
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Invalid Visa Card Address";
					}
				}
				break;
			case self::TYPE_ISMASTERCARD:
				$pattern = "/^6011[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/i";
				if(preg_match($pattern,$value)){
					$match = true;
				}else{
					if(!isset($msg)){
						$msg = "Invalid Visa Card Address";
					}
				}
				break;

			case self::TYPE_INMODEL:
				$found = false;
				foreach($array as $model => $var){
					$obj = new $model;
					$varas = get_object_vars($obj);
					if(isset($var) && in_array($var,array_keys($varas))){

						$data = $obj->read(array($var));
						foreach($data as $row){
							if(Model::is_assoc($obj->_fields)){
								if(!empty($obj->_fields[$var])){
									if($row[$obj->_fields[$var]] == $value){
										$found = true;
										break;
									}
								}else{
									if($row[$var] == $value){
										$found = true;
										break;
									}
								}
							}else{
								if(self::debug){
									echo $row[$var] . " --- " . $value . "<br>";
								}
								if($row[$var] == $value){
									$found = true;
									break;
								}
							}
						}
					}
				}
				if($found){
					$match = true;
				}else{
					$match = false;
					if(!isset($msg)){
						$msg = "$value In " . implode('=>',$array);
					}
				}
				break;
			case self::TYPE_NOTINMODEL:
				$found = false;
				foreach($array as $model => $var){
					$obj = new $model;
					$varas = get_object_vars($obj);
					if(isset($var) && in_array($var,array_keys($varas))){

						$data = $obj->read(array($var));
						foreach($data as $row){
							if(Model::is_assoc($obj->_fields)){
								if(!empty($obj->_fields[$var])){
									if($row[$obj->_fields[$var]] == $value){
										$found = true;
										break;
									}
								}else{
									if($row[$var] == $value){
										$found = true;
										break;
									}
								}
							}else{
								if(self::debug){
									echo $row[$var] . " --- " . $value . "<br>";
								}
								if($row[$var] == $value){
									$found = true;
									break;
								}
							}
						}
					}
				}
				if($found){
					$match = false;
					if(!isset($msg)){
						$msg = "$value In " . implode('=>',$array);
					}
				}else{
					$match = true;
				}
				break;
			default:
				if(self::debug){
					echo "Rule: NOT MATCH <br>";
				}
				$match = "No Rule Matched";
				break;
		}
		if($match){
			return "";
		}else{
			return $msg;
		}
	}
}
?>
