<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */

require dirname(__FILE__).'/UI/Styles/ModelStyles.php';
require dirname(__FILE__).'/UI/Styles/ModelStyleParent.php';
require dirname(__FILE__).'/UI/Styles/ModelStyles_Table.php';
require dirname(__FILE__).'/UI/Styles/ModelStyles_Form.php';

require dirname(__FILE__).'/UI/Util/HrefUtil.php';
require dirname(__FILE__).'/UI/Util/UIUtil.php';
require dirname(__FILE__).'/UI/Util/UIConstants.php';

require dirname(__FILE__).'/UI/TableUI.php';
require dirname(__FILE__).'/UI/FormUI.php';

require dirname(__FILE__).'/UI/Util/ExportUtil.php';


class UI {


	const STYLE_LIGHT_GREY = "LightGrey";
	const STYLE_SIMPLE = "Simple";
	const STYLE_APPMII = "Appmii";
	const STYLE_WP = "Wordpress";

	const UI_REQUEST_ID = "uiID";

	public $design = self::STYLE_LIGHT_GREY;


	public $tableUI;
	public $formUI;

	private $obj;
	private $style;
	public $result = array();
	public function __construct($obj,$design = ''){
		$this->obj = $obj;
		if(empty($design)){
			$design = $this->design ;
		}
		require_once dirname(__FILE__)."/UI/Styles/{$design}/{$design}.php";
		$this->style = new $design;
		$this->tableUI = new TableUI($obj,$design);
		$this->formUI = new FormUI($obj,$design);
	}

	public function generate(){
		$this->tableUI->generate();
		$this->formUI->process($this->result);
		if(isset($_REQUEST[UIConst::UI_FORM_TASK_NAME])){
			$this->formUI->setDefaultURLParams(
			array(
			UIConst::UI_FORM_TASK_NAME=>$_REQUEST[UIConst::UI_FORM_TASK_NAME],
			UIConst::UI_FORM_OBJ_NAME=>get_class($this->obj)
			)
			);
			if($_REQUEST[UIConst::UI_FORM_TASK_NAME] == UIConst::UI_FORM_TASK_EDIT){
				$this->formUI->setFormType(get_class($this->obj).'_'.UIConst::UI_FORM_TYPE_EDIT);
			}else if($_REQUEST[UIConst::UI_FORM_TASK_NAME] == UIConst::UI_FORM_TASK_DELETE){
				$this->formUI->setFormType(get_class($this->obj).'_'.UIConst::UI_FORM_TYPE_DELETE);
			}else if($_REQUEST[UIConst::UI_FORM_TASK_NAME] == UIConst::UI_FORM_TASK_INSERT){
				$this->formUI->setFormType(get_class($this->obj).'_'.UIConst::UI_FORM_TYPE_NEW);
			}
			if($_REQUEST[UIConst::UI_FORM_TASK_NAME] == UIConst::UI_FORM_TASK_EDIT && $this->showForm()){
				$key = $this->obj->getModelKeyCol();
				$this->obj->$key = $_REQUEST[UIConst::UI_FORM_ITEM_ID];
				$data = $this->obj->read();
				if(sizeof($data) > 0){
					$this->obj->smartAssign($data[0]);
				}
				$this->formUI->setDefaultURLParams(array(UIConst::UI_FORM_ITEM_ID=>$_REQUEST[UIConst::UI_FORM_ITEM_ID]));
			}
		}


	}
	public function generateTable($sortImg = true){
		return $this->tableUI->generateTable($sortImg);
	}
	public function generateForm(){
		return $this->formUI->generateForm($this->result);
	}
	public function smartUI(){
		$this->tableUI->enableBulkOperation = true;
		$this->tableUI->enableCheckboxes = true;
		$showForm = $this->showForm();

		if($showForm){
			$html = $this->formUI->generateForm($this->result);
			return $html;
		}else{
			$html = $this->tableUI->generateTable(true,$this->result);
			return $html;
		}
	}

	private function showForm(){
		$showForm = false;
		if(isset($_REQUEST[UIConst::UI_FORM_TASK_NAME])){
			if(empty($this->result)){
				$showForm = true;
			}else{
				if(isset($this->result[UIConst::UI_ERROR_MESSAGE])){
					$showForm = true;
				}else if(isset($_REQUEST[UIConst::UI_FORM_DELETE_FILE])){
					$showForm = true;
				}else{
					$showForm = false;
				}
			}
		}else{
			$showForm = false;
		}
		return $showForm;
	}

	/**
	 * Form Function
	 */
	public function setFormHeading($heading){
		$this->formUI->setFormHeading($heading);
	}

	public function setFormFieldMapping($map){
		$this->formUI->setFormFieldMapping($map);
	}
	public function setValueMap($map){
		$this->formUI->setValueMap($map);
	}

	public function setFormMethod($method){
		$this->formUI->setFormMethod($method);
	}
	public function setFormAction($action){
		$this->formUI->setFormAction($action);
	}
	public function setFormEnctype($type){
		$this->formUI->setFormEnctype($type);
	}

	public function setFormNameMap($map){
		$this->formUI->setFormNameMap($map);
	}
	public function setFormValueMap($map){
		$this->formUI->setFormValueMap($map);
	}
	public function setFormAssignMapFunc($map){
		$this->formUI->setFormAssignMapFunc($map);
	}
	public function setFormAssignHTMLFunc($map){
		$this->formUI->setFormAssignHTMLFunc($map);
	}
	public function setFormFieldProp($map){
		$this->formUI->setFormFieldProp($map);
	}
	public function setFormTypeProp($map){
		$this->formUI->setFormTypeProp($map);
	}
	public function setFormRequireMap($map){
		$this->formUI->setFormRequireMap($map);
	}
	public function setAllFormFieldProp($prop){
		$this->formUI->setAllFormFieldProp($prop);
	}
	public function setAllTypeProp($prop){
		$this->formUI->setAllTypeProp($prop);
	}
	public function setFormValueMapFunc($map){
		$this->formUI->setFormValueMapFunc($map);
	}
	public function addFormFields($fields){
		$this->formUI->addFormFields($fields);
	}
	public function addFormButton($array){
		$this->tableUI->addFormButton($array);
	}
	public function addFormModel($map){
		$this->formUI->addFormModel($map);
	}
	public function setFormFieldOrder($map){
		$this->formUI->setFormFieldOrder($map);
	}
	public function setFormFileUploadProperties($map){
		$this->formUI->setFileUploadProperties($map);
	}
	public function setMessages($map){
		$this->formUI->setMessages($map);
	}
	public function setEnableFormButton($array){
		$this->tableUI->setEnableFormButton($array);
	}
	public function setFormType($type){
		$this->formUI->setFormType($type);
	}

	/**
	 * Sets the default parameters that will be send when the page is submitted or refreshed due to action by the Table.This
	 * can be used to set all those paramets that you need to get your page working even after the form for the UI is submitted.
	 *
	 * @param $array An array of key value pairs where the keys are the parameters and the values are the value for those parameters.
	 */
	public function setDefaultURLParams($array){
		$this->tableUI->setDefaultURLParams($array);
		$this->formUI->setDefaultURLParams($array);
	}


	/**
	 * Table UI Functions
	 */

	/**
	 * Sets the name heading that will be displayed for a particular column.
	 *
	 * @param $array An array of key value pairs where the keys are the column names and values the name that should be displayed.
	 */
	public function setColumnNameMapping($array){
		$this->tableUI->setColumnNameMapping($array);
	}
	/**
	 * Hides columns from the table.
	 *
	 * @param $array An array of string containing the column names that should be hidden
	 */
	public function setHideColumn($array){
		$this->tableUI->setHideColumn($array);
	}
	/**
	 * Removes sorting functionality from particular columns
	 *
	 * @param $array An array of strings containing the column names on which sorting should be disabled
	 */
	public function setRemoveSorting($array){
		$this->tableUI->setRemoveSorting($array);
	}
	/**
	 * Specify the function to be called for a particular column.This can be used to display custom content
	 * for that particular column.The functions return value is the displayed content.The function should have two parameters.The
	 * first parameter is the value for the current cell and the second parameter contains the data for the entire row.
	 *
	 * @param $array An array of key value pair where the keys are the column names and the values are the function names.
	 */
	public function setColumnMapping($array){
		$this->tableUI->setColumnMapping($array);
	}
	/**
	 * Sets wich columns should be used for filtering.
	 *
	 * @param $array An array of string containing the column names which will be used to filter the table data.
	 */
	public function setFilterColumn($array){
		$this->tableUI->setFilterColumn($array);
	}
	/**
	 * Don't know whats it meant for
	 * @param $prop
	 * @return unknown_type
	 * @deprecated I dont think this function has any use now and might be removed in the future
	 */
	public function setProperties($prop){
		$this->tableUI->setProperties($prop);
	}
	/**
	 * Sets the action column.
	 *
	 * @param $array An array of key value pairs where keys are the action and values are an array
	 * which define what the action does.This array should contin key value pairs.Currently it supports two keys
	 * "href" and "function".The value for href is the link for that action and the value for function should point to
	 * a function.This function should return true or false.Depending on this value the action will be either displayed or hidden.
	 */
	public function setActionColumn($array){
		$this->tableUI->setActionColumn($array);
	}
	/**
	 * This is used to join two tables.
	 *
	 * @param $array An array of key value pairs where key value pairs signify the relation.The column names should not be the actual
	 * column names in the DB but the variables from the model for that table.
	 */
	public function AddModel($array){
		$this->tableUI->AddModel($array);
	}
	/**
	 * Sets the ordering for the columns display.
	 *
	 * @param $array An array containingkey value pairs where key is the column name and the values are numbers
	 * difining the position for that column.The positions are not Zero based.
	 */
	public function setColumnOrder($array){
		$this->tableUI->setColumnOrder($array);
	}
	/**
	 * Adds new column(s) in the table.
	 *
	 * @param $array An array of key value pairs. The keys would be the column names
	 * and the values would be the function names to which that particular column will be mapped.
	 *
	 * function func($row){
	 * }
	 */
	public function addCustomColumn($array){
		$this->tableUI->addCustomColumn($array);
	}
	/**
	 * Define which columns to show for a particular table / model class that has been used in the table.This
	 * is an easier way to hide all columns when only the some columns are required to display.
	 *
	 * @param $array An array of column names that needs to be displayed. The column names has to be prefixed
	 * with there correspondig class names for this to work.
	 */
	public function addShowOnlyModel($array){
		$this->tableUI->addShowOnlyModel($array);
	}

	/**
	 * Adds a function which can decide whether a particular row will be displayed or not.The row will be displayed
	 * if the function returns true and will not be displayed if it returns false
	 *
	 * @param $func String Name of a function to use as the filter
	 */
	public function addRowFilter($func){
		$this->tableUI->addRowFilter($func);
	}
	/**
	 * This can be used to pass a custom where clause for the sql querry that is generating the table.This has the
	 * same syntax as used in the where clause for read funcion of SmartModel
	 *
	 * @param $array An array containing key value pairs in the same way as passed in the where for read function
	 */
	public function addCustomFilter($array){
		$this->tableUI->addCustomFilter($array);
	}
	/**
	 * Displays a custom html when the table is empty.This function is really tricky :)
	 *
	 * @param $html String The html to display.
	 * @deprecated Has no Utility.Might be discontinued in the future.
	 */
	public function setEmptyTableHTML($html){
		$this->tableUI->setEmptyTableHTML($html);
	}
	/**
	 * Display message when the table is empty.
	 *
	 * @param $msg String The message to be displayed when the table is empty.This
	 * string can also be an html content.
	 */
	public function setEmptyTableMessage($msg){
		$this->tableUI->setEmptyTableMessage($msg);
	}
	public function setCheckBoxProp($array){
		$this->tableUI->setCheckBoxProp($array);
	}
	public function addCustomSearchManager($array){
		$this->tableUI->addCustomSearchManager($array);
	}
	public function addSearchField($array){
		$this->tableUI->addSearchField($array);
	}

}