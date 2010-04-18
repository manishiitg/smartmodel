<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */
class FileUtil {
	public $debug = false;

	const ERROR1 = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
	const ERROR2 = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
	const ERROR3 = "The uploaded file was only partially uploaded";
	const ERROR4 = "No file was uploaded";
	const ERROR5 = "Missing a temporary folder. No temp folder to upload file";
	const ERROR6 = "Failed to write file to disk";
	const ERROR7 = "File upload stopped by extension";
	const ERROR_FILTER = "File Type Didnt Match Filter";
	const ERROR_FOLDER = "Folder Doesnt Exist";

	private $props = array();
	const PROP_REQUIRED = 'required';
	const PROP_FILTER = 'filter';
	const PROP_FOLDER = 'folder';
	const PROP_UNIQUE = 'unique';
	const PROP_SANITIZE = 'sanitize';
	const PROP_FULLPATH = 'fullPath';

	private $result;
	const RESULT_ERROR = "ERROR";
	const RESULT_FINALNAME = "FINALNAME";
	const RESULT_TYPE = "TYPE";
	const RESULT_NAME = "NAME";
	/**
	 * Structure Of Array
	 *
	 *$array['file1'] = array(
	 'required'=>'true', //If this file is not in $_FILES generate error message
	 'filter'=>'mp3', //String or an array
	 'folder'=>'path', //Path where to upload this file
	 'unique' => 'true', //Make the filename stored in model as unique
	 'sanitize' => 'true', // Remove white spaces and brackets from filename
	 'fullPath' => 'true' //In the model set full path of file
		);
	 * @param unknown_type $array
	 */
	public function setUploadProperties($array){

		$this->props = $array;
	}

	/**
	 *	$folder should contain "/" at the end
	 * @param unknown_type $model
	 * @param unknown_type $folder
	 * @return unknown_type
	 */
	public function smartUpload(&$model,$folder = ''){
		if(isset($_FILES) && !empty($_FILES)){
			$obj = get_object_vars($model);
			foreach($obj as $k => $v){
				if(isset($_FILES[$k])){
					$error = $_FILES[$k]['error'];
					if($error ==   UPLOAD_ERR_INI_SIZE){
						$error =  self::ERROR1." Error Code: $error";
					}else if($error ==  UPLOAD_ERR_FORM_SIZE){
						$error =  self::ERRRO2." Error Code: $error";
					}else if($error ==  UPLOAD_ERR_PARTIAL){
						$error =  self::ERROR3." Error Code: $error";
					}else if($error ==  UPLOAD_ERR_NO_FILE){
						if(isset($this->props[$k][self::PROP_REQUIRED]) && $this->props[$k][self::PROP_REQUIRED] == true){
							$error =  self::ERROR4." Error Code: $error";
						}else{
							$error = '';
						}
					}else if($error == UPLOAD_ERR_NO_TMP_DIR){
						$error =  self::ERROR5." Error Code: $error";
					}else if($error == UPLOAD_ERR_CANT_WRITE){
						$error =  self::ERROR6." Error Code: $error";
					}else if($error == UPLOAD_ERR_EXTENSION){
						$error =  self::ERROR7." Error Code: $error";
					}
					else{
						$error = '';
						$this->result[$k][self::RESULT_TYPE] = $_FILES[$k]['type'];
						$this->result[$k][self::RESULT_NAME] = $_FILES[$k]['name'];

						if( (isset($this->props[$k][self::PROP_SANITIZE]) && $this->props[$k][self::PROP_SANITIZE] == true)){
							$name = str_replace(" ","_",$this->result[$k][self::RESULT_NAME]);
							$name = str_replace("(","",$name);
							$name = str_replace(")","",$name);
							$name = str_replace("&","",$name);
							$name = str_replace("'","",$name);
							$this->result[$k][self::RESULT_FINALNAME] = $name;
						}else{
							$this->result[$k][self::RESULT_FINALNAME] = $this->result[$k][self::RESULT_NAME];
						}


						if(isset($this->props[$k][self::PROP_FILTER])){
							$filter = $this->props[$k][self::PROP_FILTER];
							if(is_array($filter)){
								$found = false;
								foreach($filter as $f){
									if(strpos($this->result[$k][self::RESULT_TYPE],$f) !== false){
										$found = true;
									}
								}
								if(!$found){
									$error =  self::ERROR_FILTER ." Type: " . $this->result[$k][self::RESULT_TYPE];
								}
							}else{
								if(strpos($this->result[$k][self::RESULT_TYPE],$filter) === false){
									$error =  self::ERROR_FILTER;
								}
							}
						}

						if(isset($this->props[$k][self::PROP_UNIQUE]) && $this->props[$k][self::PROP_UNIQUE] ) {
							$uid = uniqid();
							$this->result[$k][self::RESULT_FINALNAME] = $uid.$this->result[$k][self::RESULT_FINALNAME];
						}
						if(isset($this->props[$k][self::PROP_FOLDER])){
							$folder = $this->props[$k][self::PROP_FOLDER];
						}
						if(!file_exists($folder) && !empty($folder)){
							$error =  self::ERROR_FOLDER;
						}
						if(move_uploaded_file($_FILES[$k]['tmp_name'], $folder.$this->result[$k][self::RESULT_FINALNAME])){
							if($this->debug){
								echo 'File Sucessfully Moved to '. $folder.$this->result[$k][self::RESULT_FINALNAME];
							}
							if(isset($this->props[$k][self::PROP_FULLPATH]) && $this->props[$k][self::PROP_FULLPATH] == true){
								$model->$k = $folder.$this->result[$k][self::RESULT_FINALNAME];
							}else{
								$model->$k = $this->result[$k][self::RESULT_FINALNAME];
							}
						}else{
							$error =  'Error While Moving File To Folder ' .$folder ;
						}
					}
					$this->result[$k][self::RESULT_ERROR] = $error;
				}else{
					if(isset($this->props[$k][self::PROP_REQUIRED]) && $this->props[$k][self::PROP_REQUIRED] == true){
						$this->result[$k][self::RESULT_ERROR] = $model->getColumnName($k) .  ' Is Required';
					}
				}
				if($this->debug && isset($this->result[$k])){
					print_r($this->result[$k]);
				}
			}
		}else {
			if($this->debug){
				echo 'File Upload Called But $_FILES is not set';
			}
		}
	}
	public function getResult(){
		return $this->result;
	}
}