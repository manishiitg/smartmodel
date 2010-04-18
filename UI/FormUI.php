<?php

require dirname(__FILE__).'/Util/FormProcessing.php';
class FormUI{

	const FORM_FIELD_HIDDEN = "HIDDEN";
	const FORM_FIELD_TEXTAREA= "TEXTAREA";
	const FORM_FIELD_TEXTFIELD = "TEXTFIELD";
	const FORM_FIELD_PASSWORD = "PASSWORD";
	const FORM_FIELD_CHECKBOX = "CHECKBOX";
	const FORM_FIELD_DROPDOWN = "DROPDOWN";
	const FORM_FIELD_RADIO = "RADIO";
	const FORM_FIELD_MULTISELECT = "MULTISLECT";
	const FORM_FIELD_FILEUPLOAD = "FILE";
	const FORM_FIELD_NOTSHOW = "DONTSHOW";

	public $id = 0;
	public function __construct($obj,$design){
		$this->hrefUtil = new HrefUtil();
		$this->obj = $obj;
		require_once dirname(__FILE__)."/Styles/{$design}/{$design}.php";
		if(file_exists(dirname(__FILE__)."/Styles/{$design}/{$design}_Form_{$this->formType}.php")){
			require_once dirname(__FILE__)."/Styles/{$design}/{$design}_Form_{$this->formType}.php";
			$t = $design."_Form_{$this->formType}";
			$this->style = new $t;
		}else{
			require_once dirname(__FILE__)."/Styles/{$design}/{$design}_Form.php";
			$t = $design."_Form";
			$this->style = new $t;
		}
		$this->baseStyle = new $design;
		$this->action = $_SERVER['PHP_SELF'];
		$this->method = "POST";
	}
	public function process(&$array){
		FormProcessing::process($array,$this);
	}
	public function generateForm($result = null){
		$obj = $this->obj;
		FormProcessing::assign($obj,$this);
		$fields = $obj->getKeys();
		foreach($fields as $f){
			if(isset($obj->$f) && !empty($obj->$f)){
				if(!isset($this->formValueMap[$f]))
				$this->formValueMap[$f] = $obj->$f;
			}
			if(!isset($this->formNameMap[$f])){
				$this->formNameMap[$f] = $obj->getColumnName($f);
			}
		}
		if(!empty($this->addModel)){
			foreach($this->addModel as $key=>$value){
				$arr = UIUtil::getClassVar($value);
				$model = $arr[UIConst::UI_TABLE];
				$var = $arr[UIConst::UI_VAR];
				$class = new $model;
				FormProcessing::assign_model($this->obj,$class,$var,$this);
				$fields1 = $class->getKeys();
				foreach($fields1 as $f){
					$f1 = get_class($class) . '.' . $f;
					if($f1 == $value){
						$this->setFormFieldMapping(array($f1=>FormUI::FORM_FIELD_NOTSHOW));
					}
					array_push($fields,$f1);
					if(isset($class->$f) && !empty($class->$f)){
						if(!isset($this->formValueMap[$f1]))
						$this->formValueMap[$f1] = $class->$f;
					}
					if(!isset($this->formNameMap[$f1])){
						$this->formNameMap[$f1] = $class->getColumnName($f);
					}
				}
			}
		}
		if(!empty($this->addFields)){
			$fields = array_merge($fields,$this->addFields);
		}
		if(!empty($this->order)){
			$columnNameOrdered = array();
			$addedNames = array();
			foreach($fields as $field){
				if(isset($this->order[$field])){
					$i = $this->order[$field];
					$columnNameOrdered[$i-1] = $field;
					$addedNames[] = $field;
				}
			}
			$i=0;
			$j=0;
			$columnName = array();
			for($i=0;$i<sizeof($fields);$i++){
				if(isset($columnNameOrdered[$i]) && !empty($columnNameOrdered[$i])){
					$columnName[$j] = $columnNameOrdered[$i];
					unset($columnNameOrdered[$i]);
					$i--;
				}else{
					if(!in_array($fields[$i],$addedNames)){
						$columnName[$j] = $fields[$i];
					}
				}
				$j++;
			}
			$fields = $columnName;
		}
		$html = $this->generateDataForm($fields,get_class($obj),$result);
		return $html;
	}
	public function generateDataForm($fields,$heading = '',$result = null){
		if(!empty($this->heading)){
			$heading = $this->heading;
		}

		UIUtil::generateUniqID($this);
		foreach($this->hrefUtil->defaultURLParams as $k => $v){
			$fields[] = $k;
			$this->fieldMap[$k] = self::FORM_FIELD_HIDDEN;
			$this->formValueMap[$k] = $v;
		}

		$fields_str = "";
		$hidden_str = "";
		$button_str = "";

		foreach($fields as $f){
			$ui = "";
			if(isset($this->fieldMap[$f]) && $this->fieldMap[$f] == self::FORM_FIELD_HIDDEN ){
				$hidden_str .= $this->getFieldTypeUI($f);
			}else if(isset($this->fieldMap[$f]) && $this->fieldMap[$f] == self::FORM_FIELD_NOTSHOW){
			}
			else{

				$ui = $this->getFieldNameUI	($f);
				$ui .= $this->getFieldTypeUI($f);
			}

			$fields_str .= $this->style->getFormField();
			$fields_str = str_replace('{field}',$ui,$fields_str);
		}

		$button_str = $this->getFormButtonUI();

		$html = $this->style->getFormHTMLStrcuture();
		$html = str_replace("{FORM_FIELDS}",$fields_str,$html);
		$html = str_replace("{HIDDEN_TYPE}",$hidden_str,$html);
		$html = str_replace("{BUTTONS}",$button_str,$html);
		$html = str_replace("{heading}",$heading,$html);
		$html = str_replace("{method}",$this->method,$html);
		$html = str_replace("{enctype}",$this->enctype,$html);
		$html = str_replace("{action}",$this->action,$html);

		$res = UIUtil::checkMessages($result,$this->baseStyle);
		$html = str_replace("{MESSAGES}",$res,$html);

		$scripts = $this->style->loadScripts();
		return $scripts.$html;
	}

	private function getFormButtonUI(){
		$html = $this->style->getButtonField();

		$button1 = $this->style->getButton(UIConst::UI_FORM_SUBMIT);
		$button2 = $this->style->getButton(UIConst::UI_FORM_RESET);

		$buttons = $button1.$button2;
		$html = str_replace('{buttons}',$buttons,$html);
		return $html;
	}

	private function getFieldNameUI($f){
		$require = true;
		if(isset($this->formRequiredMap[$f])){
			$require = $this->formRequiredMap[$f];
		}
		$label = $this->style->getFormLabelHTML($require);
		$label = str_replace('{inputname}',$f,$label);

		$prop = "";
		$input_type = self::FORM_FIELD_TEXTFIELD;
		if( isset($this->fieldMap[$f]) ){
			$input_type = $this->fieldMap[$f];
		}
		if(isset($this->formFieldProp[$f])){
			$prop = $this->formFieldProp[$f];
		}else {
			$prop = $this->allFieldProp;
		}
		$label = str_replace('{prop}',$prop,$label);

		if(isset($this->formNameMap[$f])){
			$f =  $this->formNameMap[$f];
		}
		$label = str_replace('{name}',$f,$label);
		return $label;
	}
	private function getFieldTypeUI($f){
		$input_type = self::FORM_FIELD_TEXTFIELD;
		if( isset($this->fieldMap[$f]) ){
			$input_type = $this->fieldMap[$f];
		}
		$type = $this->style->getInputHTML($input_type);
		if($input_type == self::FORM_FIELD_MULTISELECT){
			$type = str_replace('{name}',$f.'[]',$type);
		}else{
			$type = str_replace('{name}',$f,$type);
		}


		$value = "";
		if(isset($this->formValueMap[$f])){
			$value = $this->formValueMap[$f];
		}
		if(isset($this->customAssign[$f])){
			$func = $this->customAssign[$f];
			$value = UIUtil::callFunction($func,$value,$this->obj,$type);
		}
		if(isset($this->formValueMapFunc[$f])){
			$func = $this->formValueMapFunc[$f];
			$values = UIUtil::callFunction($func,$this->obj);
			$values_str = "";
		}
		if($input_type == self::FORM_FIELD_DROPDOWN || $input_type == UIConst::FORM_FIELD_DROPDOWN_OTHERS || $input_type == UIConst::FORM_FIELD_DROPDOWN_TEXT){
			if(Model::is_assoc($values)){
				foreach($values as $key => $val){
					$selected = "";
					if($key == $value){
						$selected = "selected=selected";
					}
					$values_str .= "<option value='$key' $selected >$val</option>";
				}
			}else{
				foreach($values as $val){
					$selected = "";
					if($val == $value){
						$selected = "selected=selected";
					}
					$values_str .= "<option name='$val' $selected >$val</option>";
				}
			}
		}else if($input_type == self::FORM_FIELD_MULTISELECT){
			$val_arr = explode(',',$value);
			if(Model::is_assoc($values)){
				foreach($values as $key => $val){
					$selected = "";
					if(in_array($key,$val_arr)){
						$selected = "selected=selected";
					}
					$values_str .= "<option value='$key' $selected >$val</option>";
				}
			}else{
				foreach($values as $val){
					$selected = "";
					if(in_array($key,$val_arr)){
						$selected = "selected=selected";
					}
					$values_str .= "<option name='$val' $selected >$val</option>";
				}
			}
		}else if($input_type == self::FORM_FIELD_CHECKBOX){
			if($value){
				$value = "checked=checked";
			}
		}else{
			if(isset($values) && !empty($values))
			$value = $values;
		}
		$value = empty($values_str) ? $value : $values_str;

		if(isset($this->customAssignHTML[$f])){
			$func = $this->customAssignHTML[$f];
			$type = UIUtil::callFunction($func,$value,$this->obj,$type);
		}
		else if($input_type == self::FORM_FIELD_FILEUPLOAD){
			$html = "";
			if(!empty($value)){
				$href = $this->hrefUtil->getFileDeleteURL($f,$this->obj);
				$html = "$value <a href='$href'>Delete</a>";
			}
			$type = str_replace('{EDIT}',$html,$type);
			$type = str_replace('{value}',"",$type);
		}else{
			$type = str_replace('{value}',$value,$type);
		}

		$prop = "";
		if(isset($this->formTypeProp[$input_type])){
			$prop = $this->formTypeProp[$input_type];
		}else if(isset($this->formTypeProp[$f])){
			$prop = $this->formTypeProp[$f];
		}else{
			$prop = $this->allTypeProp;
		}

		$type = str_replace('{prop}',$prop,$type);
		return $type;
	}


	/**
	 * User Accesiable Variables
	 * @return unknown_type
	 */
	public function setDefaultURLParams($array){
		if(empty($this->hrefUtil->defaultURLParams))
		$this->hrefUtil->defaultURLParams = $array;
		else{
			$this->hrefUtil->defaultURLParams = array_merge($this->hrefUtil->defaultURLParams,$array);
		}
	}
	public function setFormHeading($heading){
		$this->heading = $heading;
	}

	public function setFormFieldMapping($map){
		$this->fieldMap = array_merge($map,$this->fieldMap);
	}
	public function getFormFieldMapping(){
		return $this->fieldMap;
	}
	public function setFormMethod($method){
		$this->method = $method;
	}
	public function setFormAction($action){
		$this->action = $action;
	}
	public function setFormEnctype($type){
		$this->enctype = $type;
	}

	public function setFormNameMap($map){
		$this->formNameMap =array_merge($map,$this->formNameMap);
	}
	public function setFormValueMap($map){
		$this->formValueMap = array_merge($map,$this->formValueMap);
	}
	public function setAllFormFieldProp($prop){
		$this->allFieldProp = $prop;
	}
	public function setAllTypeProp($prop){
		$this->allTypeProp = $prop;
	}
	public function setFormFieldProp($map){
		$this->formFieldProp = array_merge($map,$this->formFieldProp);
	}
	public function setFormTypeProp($map){
		$this->formTypeProp = array_merge($map,$this->formTypeProp);
	}
	public function setFormRequireMap($map){
		$this->formRequiredMap = array_merge($map,$this->formRequiredMap);
	}
	public function setFormValueMapFunc($map){
		$this->formValueMapFunc = array_merge($this->formValueMapFunc,$map);
	}
	public function setFormType($type){
		$this->formType = $type;
	}
	public function getFormType(){
		return $this->formType;
	}
	public function addFormFields($fields){
		$this->addFields = array_merge($this->addFields,$fields);
	}
	/**
	 *	This is used during custom assignment
	 * Like for a field the value that should appear, some custom function is required
	 */
	public function setFormAssignMapFunc($map){
		$this->customAssign = array_merge($this->customAssign,$map);
	}
	public function setFormAssignHTMLFunc($map){
		$this->customAssignHTML = $map;
	}
	public function addFormModel($map){
		$this->addModel = array_merge($this->addModel,$map);
	}
	public function getFormModel(){
		return $this->addModel;
	}
	public function setFormFieldOrder($map){
		$this->order = array_merge($this->order,$map);
	}
	public function setMessages($map){
		$this->message = array_merge($this->order,$map);
	}

	/**
	 * Set the file util array for any form filed
	 */
	public function setFileUploadProperties($map){
		$this->fileUploadProp = $map;
	}
	public function getFileUploadProperties(){
		return $this->fileUploadProp;
	}
	private $style;
	private $baseStyle;
	public $obj;
	private $hrefUtil;
	private $heading = "";
	private $fieldMap = array();
	private $formNameMap = array();
	private $formValueMap = array();
	private $formValueMapFunc = array();
	private $addFields = array();
	private $addModel = array();
	private $order = array();

	private $formRequiredMap = array();
	private $method = "GET";
	private $action ="";
	private $enctype = "";

	private $formFieldProp = array();
	private $formTypeProp = array();
	private $customAssign = array();
	private $customAssignHTML = array();
	private $allFieldProp = "";
	private $allTypeProp = "";
	private $formType = "";
	private $fileUploadProp = array();
	private $message = array();


}