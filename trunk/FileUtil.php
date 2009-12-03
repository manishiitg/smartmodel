<?php
/**
 * Author: Manish
 * Version: v0.2.1 alpha
 */
class FileUtil {
	const debug = false;

	/**
	 * Any File you want to apply.
	 * Basically to check if a string is present in the $_FILES[]['type']
	 * e.g if(strpos($_FILES[name][type],$filter) == false)
	 *
	 * This can be a single string or an array
	 */
	public $filter;
	/**
	 * If this is set to true, the while spaces in filename are converted to underscore
	 * @var unknown_type
	 */
	public $removeWhiteSpaces = true;
	/**
	 * If this is set to true, then a uniqid() is appended in front of the filename
	 * @var unknown_type
	 */
	public $makeUnique = true;
	/**
	 * If this is set to true the the full file name is stored in object,
	 * ie $folder/$filename
	 * The default is only the filename
	 * @var unknown_type
	 */
	public $fullPath = false;

	private $name;
	private $type;
	private $error;
	private $finalName;

	const ERROR1 = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
	const ERROR2 = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
	const ERROR3 = "The uploaded file was only partially uploaded";
	const ERROR4 = "No file was uploaded";
	const ERROR5 = "Missing a temporary folder. No temp folder to upload file";
	const ERROR6 = "Failed to write file to disk";
	const ERROR7 = "File upload stopped by extension";
	const ERROR_FILTER = "File Type Didnt Match Filter";
	const ERROR_FOLDER = "Folder Doesnt Exist";

	/**
	 *	$folder should contain "/" at the end
	 * @param unknown_type $model
	 * @param unknown_type $folder
	 * @return unknown_type
	 */
	public function smartUpload(&$model,$folder){
		if(isset($_FILES)){
			$obj = get_object_vars($model);
			foreach($obj as $k => $v){
				if(isset($_FILES[$k])){


					$this->error = $_FILES[$k]['error'];
					if($this->error ==   UPLOAD_ERR_INI_SIZE){
						return self::ERROR1;
					}else if($this->error ==  UPLOAD_ERR_FORM_SIZE){
						return self::ERRRO2;
					}else if($this->error ==  UPLOAD_ERR_PARTIAL){
						return self::ERROR3;
					}else if($this->error ==  UPLOAD_ERR_NO_FILE){
						return self::ERROR4;
					}else if($this->error == UPLOAD_ERR_NO_TMP_DIR){
						return self::ERROR5;
					}else if($this->error == UPLOAD_ERR_CANT_WRITE){
						return self::ERROR6;
					}else if($this->error == UPLOAD_ERR_EXTENSION){
						return self::ERROR7;
					}
					else{
							
						$this->type = $_FILES[$k]['type'];
						$this->name = $_FILES[$k]['name'];
							
						if($this->removeWhiteSpaces){
							$this->finalName = str_replace(" ","_",$this->name);
						}

						if(isset($this->filter) && !empty($this->filter)){
							if(is_array($this->filter)){
								$found = false;
								foreach($this->filter as $f){
									if(strpos($this->type,$f) !== false){
										$found = true;
									}
								}
								if(!$found){
									return self::ERROR_FILTER;
								}
							}else{
								if(strpos($this->type,$this->filter) === false){
									return self::ERROR_FILTER;
								}
							}
						}

						if($this->makeUnique){
							$uid = uniqid();
							$this->finalName = $uid.$this->finalName;
						}
						if(!file_exists($folder)){
							return self::ERROR_FOLDER;
						}
						if(move_uploaded_file($_FILES[$k]['tmp_name'], $folder.$this->finalName)){
							if($this->fullPath){
								$model->$k = $folder.$this->finalName;
							}else{
								$model->$k = $this->finalName;
							}
							if(self::debug){
								echo 'File Sucessfully Moved to '. $folder.$this->finalName;
							}
						}else{
							if(self::debug){
								echo 'Error While Moving File To Folder ' .$folder ;
							}
						}
					}
				}
			}
		}else {
			if(self::debug){
				echo 'File Upload Called But $_FILES is not set';
			}
		}
	}
	public function getName(){
		return $this->name;
	}
	public function getType(){
		return $this->type;
	}
	public function getErrorCode(){
		return $this->error;
	}
	public function getFinalName(){
		return $this->finalName;
	}
}