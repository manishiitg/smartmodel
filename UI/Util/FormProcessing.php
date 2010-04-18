<?php

class FormProcessing {
	const CLASS_NAME_1 = "SmartForm_{obj}_{type}Manager";
	const CLASS_NAME_2 = "SmartForm_{obj}Manager";
	const CLASS_NAME_3 = "SmartForm_{type}Manager";
	/**
	 * methods are insert,edit, delete, clone,assign
	 const METHOD_NAME_ASSIGN = "{method}_assign(obj,&result)";
	 const METHOD_NAME_VALIDATE_BEFORE = "{method}_validate_before(obj,&result)";
	 const METHOD_NAME_VALIDATE = "{method}_validate(obj,&result)";
	 const METHOD_NAME_VALIDATE_FAILURE = "{method}_validate_fail(obj,&result)";
	 const METHOD_NAME_PROCESS = "{method}_process(obj,&result)";
	 const METHOD_ADDMOEL = {method}_addmodel($obj,$modelList,$result,$insertID)
	 **/

	public static function deleteFile(&$array,$formUI){
		$manager_obj = self::getManager($formUI);
		$class = $_REQUEST[UIConst::UI_FORM_OBJ_NAME];
		$obj = new $class;
		$ids = $_REQUEST[UIConst::UI_FORM_ITEM_ID];

		if($manager_obj && method_exists($manager_obj,'deleteFile_process')){
			$manager_obj->delete_process($obj,$ids,$array);
		}else{

			$key = $obj->getModelKeyCol();
			$obj->$key = $ids;

			$col = $_REQUEST[UIConst::UI_FORM_DELETE_FILE];
			$data = $obj->read();

			if(sizeof($data) > 0){
				$row = $data[0];
				$file = $row[$col];
				if(file_exists($file) && !is_dir($file)){
					unlink($file);
				}else {
					$map = $formUI->getFileUploadProperties();
					if(isset($map[$col][FileUtil::PROP_FOLDER])){
						$file = $map[$col][FileUtil::PROP_FOLDER] . $file;
						if(file_exists($file) && !is_dir($file)){
							unlink($file);
						}
					}
				}

				$arr['where'] =  array($key => $ids);
				$arr['update'] =  array($col => "");
				$rows = $obj->smartUpdate($arr);

				$array[UIConst::UI_SUCESS_MESSAGE] = 'Sucessfully Removed File';
				$array[UIConst::UI_ROWS_UPDATED] = 1;
			}
		}
	}

	public static function assign_model($obj,&$model,$var,$formUI){
		if(isset($_REQUEST[UIConst::UI_FORM_TASK_NAME]) && $_REQUEST[UIConst::UI_FORM_TASK_NAME] == UIConst::UI_FORM_TASK_EDIT){
			$key = $model->getModelKeyCol();
			if(isset($obj->$key)){
				$model->$var = $obj->$key;
				$data = $model->read();
				if(sizeof($data) > 0){
					$model->smartAssign($data[0]);
				}
			}
		}
	}
	/**
	 * This is called just before form is generated, this method can be used to put in custom values in the form
	 */
	public static function assign(&$obj,&$formUI){
		$manager_obj = self::getManager($formUI);
		if($manager_obj && method_exists($manager_obj,'assign')){
			$manager_obj->assign($obj,$formUI);
		}else{
			$obj->smartAssign();
		}
	}

	public static function insertObject(&$array,$formUI){
		$manager_obj = self::getManager($formUI);
		$class = $_REQUEST[UIConst::UI_FORM_OBJ_NAME];
		$obj = new $class;

		if(isset($_FILES)){
			$fileUtil = new FileUtil();
		}


		if($manager_obj && method_exists($manager_obj,'insert_assign')){
			$manager_obj->insert_assign($obj,$array);
		}else{
			$obj->smartAssign();
			if(isset($_FILES)){
				$map = $formUI->getFileUploadProperties();
				$fileUtil->setUploadProperties($map);
				$fileUtil->smartUpload($obj);
			}
		}


		if($manager_obj && method_exists($manager_obj,'insert_validate_before')){
			$manager_obj->insert_validate_before($obj,$array);
		}


		$validation_result = false;
		if($manager_obj && method_exists($manager_obj,'insert_validate')){
			$validation_result = $manager_obj->insert_validate($obj,$array);
		}else{
			$validation_result = !$obj->validate();
			if(isset($_FILES)){
				$map = $formUI->getFormFieldMapping();
				$result = $fileUtil->getResult();
				foreach($map as $key => $val){
					if($val == FormUI::FORM_FIELD_FILEUPLOAD){
						if(isset($result[$key][FileUtil::RESULT_ERROR]) && !empty($result[$key][FileUtil::RESULT_ERROR])){
							$validation_result = true;
						}
					}
				}
			}

		}
		if($validation_result){
			if($manager_obj && method_exists($manager_obj,'insert_validate_fail')){
				$manager_obj->insert_validate_fail($obj,$array);
			}else{
				$error_message = $obj->getValidationError();
				if(!isset($error_message)){
					$error_message = "";
				}
				if(isset($_FILES)){
					$map = $formUI->getFormFieldMapping();
					$result = $fileUtil->getResult();
					foreach($map as $key => $val){
						if($val == FormUI::FORM_FIELD_FILEUPLOAD){
							if(isset($result[$key][FileUtil::RESULT_ERROR])){
								$error_message .= $result[$key][FileUtil::RESULT_ERROR] . "<br>";
							}
						}
					}
				}
				if(!isset($array[UIConst::UI_ERROR_MESSAGE]))
				$array[UIConst::UI_ERROR_MESSAGE] = $error_message;
				else{
					$array[UIConst::UI_ERROR_MESSAGE] .= $error_message;
				}
			}
		}else{
			if($manager_obj && method_exists($manager_obj,'insert_process')){
				$manager_obj->insert_process($obj,$array);
			}else{
				$id = $obj->insert();

				$sucess_message = "Successfully Inserted";

				/**
				 * ADD Model Support Not Proper
				 */

				$model =  $formUI->getFormModel();
				if(!empty($model)){
					if($manager_obj && method_exists($manager_obj,'insert_addmodel')){
						$validation_result = $manager_obj->insert_addmodel($obj,$model,$array,$id);
					}else{
						foreach($model as $key => $val){
							$arr = UIUtil::getClassVar($val);
							$class = $arr[UIConst::UI_TABLE];
							$var = $arr[UIConst::UI_VAR];
							$model = new $class;
							$model->$var = $id;
							$newarr = array();
							foreach($_REQUEST as $key => $val){
								if(strpos($key,'_') !== false){
									$key = str_replace('_','.',$key);
									$newarr[$key] = $val;
								}
							}
							$obj->smartAssign($newarr,$model);
							$model->insert();
							$sucess_message .= "<br>". get_class($model) .  " Successfully Inserted";
						}
					}
				}
				$array[UIConst::UI_SUCESS_MESSAGE] = $sucess_message;
				$array[UIConst::UI_INSERT_ID] = $id;
			}
		}
	}

	public static function editObject(&$array,$formUI){
		$class = $_REQUEST[UIConst::UI_FORM_OBJ_NAME];
		$obj = new $class;
		$manager_obj = self::getManager($formUI);
		if(isset($_FILES)){
			$fileUtil = new FileUtil();
		}
		if($manager_obj && method_exists($manager_obj,'edit_assign')){
			$manager_obj->edit_assign($obj,$array);
		}else{
			$obj->smartAssign();
			if(isset($_FILES)){
				$map = $formUI->getFileUploadProperties();
				$fileUtil->setUploadProperties($map);
				$fileUtil->smartUpload($obj);
			}
		}

		if($manager_obj && method_exists($manager_obj,'edit_validate_before')){
			$manager_obj->edit_validate_before($obj,$array);
		}

		$validation_result = false;
		if($manager_obj && method_exists($manager_obj,'edit_validate')){
			$validation_result = $manager_obj->edit_validate($obj,$array);
		}else{
			$validation_result = !$obj->validate();
			if(isset($_FILES)){
				$map = $formUI->getFormFieldMapping();
				$result = $fileUtil->getResult();
				foreach($map as $key => $val){
					if($val == FormUI::FORM_FIELD_FILEUPLOAD){
						if(isset($result[$key][FileUtil::RESULT_ERROR]) && !empty($result[$key][FileUtil::RESULT_ERROR])){
							$validation_result = true;
						}
					}
				}
			}
		}

		if($validation_result){
			if($manager_obj && method_exists($manager_obj,'edit_validate_fail')){
				$manager_obj->edit_validate_fail($obj,$array);
			}else{
				$error_message = $obj->getValidationError();
				if(!isset($error_message)){
					$error_message = "";
				}
				if(isset($_FILES)){
					$map = $formUI->getFormFieldMapping();
					$result = $fileUtil->getResult();
					foreach($map as $key => $val){
						if($val == FormUI::FORM_FIELD_FILEUPLOAD){
							if(isset($result[$key][FileUtil::RESULT_ERROR])){
								$error_message .= $result[$key][FileUtil::RESULT_ERROR] . "<br>";
							}
						}
					}
				}
				if(!isset($array[UIConst::UI_ERROR_MESSAGE])){
					$array[UIConst::UI_ERROR_MESSAGE] = $error_message;
				}else{
					$array[UIConst::UI_ERROR_MESSAGE] .= $error_message;
				}
			}
		}else{
			if($manager_obj && method_exists($manager_obj,'edit_process')){
				$manager_obj->edit_process($obj,$array);
			}else{
				$key = $obj->getModelKeyCol();
				$opt = array($key => $_REQUEST[UIConst::UI_FORM_ITEM_ID]);
				$obj->$key = null;

				if(isset($_FILES)){
					$map = $formUI->getFormFieldMapping();
					$obj1 = new $class;
					$obj1->$key = $_REQUEST[UIConst::UI_FORM_ITEM_ID];
					$data = $obj1->read();
					foreach($map as $key => $val){
						if($val == FormUI::FORM_FIELD_FILEUPLOAD){
							foreach($data as $row){
								if(isset($row[$key])){
									$file = $row[$key];
									if(isset($obj->$key) && !empty($obj->$key)){
										if(file_exists($file) && !is_dir($file)){
											unlink($file);
										}else {
											$map = $formUI->getFileUploadProperties();
											if(isset($map[$key][FileUtil::PROP_FOLDER])){
												$file = $map[$key][FileUtil::PROP_FOLDER] . $file;
												if(file_exists($file) && !is_dir($file)){
													unlink($file);
												}
											}
										}
									}else{
										$obj->$key = $file;
									}
								}
							}
						}
					}
				}
				$rows = $obj->update(null,$opt);

				$model =  $formUI->getFormModel();
				if(!empty($model)){
					if($manager_obj && method_exists($manager_obj,'edit_addmodel')){
						$id = $_REQUEST[UIConst::UI_FORM_ITEM_ID];
						$validation_result = $manager_obj->edit_addmodel($obj,$model,$array,$id);
					}else{
						foreach($model as $key1 => $val){
							$arr = UIUtil::getClassVar($val);
							$class = $arr[UIConst::UI_TABLE];
							$var = $arr[UIConst::UI_VAR];
							$model = new $class;
							//						$model->$var = $key;
							$newarr = array();
							foreach($_REQUEST as $key2 => $val){
								if(strpos($key2,'_') !== false){
									$key2 = str_replace('_','.',$key2);
									$newarr[$key2] = $val;
								}
							}
							$obj->smartAssign($newarr,$model);
							$opt= array($var => $_REQUEST[UIConst::UI_FORM_ITEM_ID]);
							$model->update(null,$opt);
							$sucess_message .= "<br>". get_class($model) .  " Successfully Inserted";
						}
					}
				}

				$array[UIConst::UI_SUCESS_MESSAGE] = 'Sucessfully Updated';
				$array[UIConst::UI_ROWS_UPDATED] = $rows;
			}

		}
	}
	public static function deleteObject(&$array,$formUI){
		$manager_obj = self::getManager($formUI);
		$class = $_REQUEST[UIConst::UI_FORM_OBJ_NAME];
		$obj = new $class;
		$ids = $_REQUEST[UIConst::UI_FORM_ITEM_ID];

		if($manager_obj && method_exists($manager_obj,'delete_process')){
			$manager_obj->delete_process($obj,$ids,$array);
		}else{

			$key = $obj->getModelKeyCol();
			$keyCol = $obj->getColumnName($key);

			if(strpos($ids,',') !== false){
				$sql = "delete from " . $obj->_table . " where $keyCol in (" . $ids . ")";
				$rows = $obj->query($sql,null,'delete');
				$array[UIConst::UI_SUCESS_MESSAGE] = 'Sucessfully Deleted';
				$array[UIConst::UI_ROWS_UPDATED] = $rows;
			}else{
				$obj->$key = $ids;
				if(isset($_FILES)){
					$map = $formUI->getFormFieldMapping();
					$sql = "select * from " . $obj->_table . " where $keyCol in (" . $ids . ")";
					$rows = $obj->query($sql,null,'select');
					$data = $obj->getData($rows);
					foreach($map as $key => $val){
						if($val == FormUI::FORM_FIELD_FILEUPLOAD){
							foreach($data as $row){
								$file = $row[$key];
								if(file_exists($file) && !is_dir($file)){
									unlink($file);
								}else {
									$map = $formUI->getFileUploadProperties();
									if(isset($map[$key][FileUtil::PROP_FOLDER])){
										$file = $map[$key][FileUtil::PROP_FOLDER] . $file;
										if(file_exists($file) && !is_dir($file)){
											unlink($file);
										}
									}
								}
							}
						}
					}
				}
				$rows = $obj->delete();

				$array[UIConst::UI_SUCESS_MESSAGE] = 'Sucessfully Deleted: ' . $rows;
				$array[UIConst::UI_ROWS_UPDATED] = $rows;
			}
		}

	}

	private static function getManager($formUI){
		$type = $formUI->getFormType();
		$obj = get_class($formUI->obj);
		$manager = self::CLASS_NAME_1;
		$manager = str_replace('{type}',$type,$manager);
		$manager = str_replace('{obj}',$obj,$manager);
		if(class_exists($manager,false)){
			$manager_obj = new $manager;
			return $manager_obj;
		}else{
			$manager = self::CLASS_NAME_2;
			$manager = str_replace('{obj}',$obj,$manager);
			if(class_exists($manager,false)){
				$manager_obj = new $manager;
				return $manager_obj;
			}else{
				$manager = self::CLASS_NAME_3;
				$manager = str_replace('{type}',$type,$manager);
				if(class_exists($manager,false)){
					$manager_obj = new $manager;
					return $manager_obj;
				}else{
					return false;
				}
			}
		}
	}

	public static function process(&$array,$formUI){
		if(isset($_REQUEST[UIConst::UI_FORM_TASK_NAME]) && isset($_REQUEST[UIConst::UI_FORM_OBJ_NAME])){
			$task = $_REQUEST[UIConst::UI_FORM_TASK_NAME];
			if($task == UIConst::UI_FORM_TASK_INSERT){
				self::insertObject($array,$formUI);
			}else if(isset($_REQUEST[UIConst::UI_FORM_DELETE_FILE])){
				self::deleteFile($array,$formUI);
			}else if($task == UIConst::UI_FORM_TASK_EDIT){
				self::editObject($array,$formUI);
			}else if($task == UIConst::UI_FORM_TASK_DELETE){
				self::deleteObject($array,$formUI);
			}else if($task == UIConst::UI_FORM_TASK_CLONE){
				self::cloneObject($array,$formUI);
			}
		}
	}
}