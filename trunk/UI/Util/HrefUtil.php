<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */
class HrefUtil {
	public $defaultURLParams = array();

	public function generateExportHref($obj,$name){
		$href = $_SERVER['PHP_SELF']. "?".UIConst::UI_EXPORT_REQUEST."=".$name;
		if($obj->checkRequest(UIConst::UI_REQUEST_SORT)){
			$href .= "&".UIConst::UI_REQUEST_SORT."=".$ui->getRequest(UIConst::UI_REQUEST_SORT);
			$href .= "&".UIConst::UI_REQUEST_COL."=".$ui->getRequest(UIConst::UI_REQUEST_COL);
		}
		if($obj->checkRequest(UIConst::UI_REQUEST_NUMBER)){
			$href .= "&".UIConst::UI_REQUEST_NUMBER."=".$ui->getRequest(UIConst::UI_REQUEST_NUMBER);
		}
		if($obj->checkRequest(UIConst::UI_REQUEST_FILTER)){
			$href .= "&".UIConst::UI_REQUEST_FILTER."=".$ui->getRequest(UIConst::UI_REQUEST_FILTER);
			$href .= "&".UIConst::UI_REQUEST_FILTER_COL."=".$ui->getRequest(UIConst::UI_REQUEST_FILTER_COL);
		}
		if($obj->checkRequest(UIConst::UI_REQUEST_SEARCH)){
			$href .= "&".UIConst::UI_REQUEST_SEARCH."=".$obj->getRequest(UIConst::UI_REQUEST_SEARCH);
		}
		if($obj->checkRequest(UIConst::UI_REQUEST_PAGE)){
			$href .= "&".UIConst::UI_REQUEST_PAGE."=".$ui->getRequest(UIConst::UI_REQUEST_PAGE);
		}
		$this->getDefaultParameters($href);
		return $href;
	}
	public function generatePagingHref($plus,$page,$ui){
		if($plus)
		$href = $_SERVER['PHP_SELF']. "?".UIConst::UI_REQUEST_PAGE."=". ($page - 1);
		else
		$href = $_SERVER['PHP_SELF']. "?".UIConst::UI_REQUEST_PAGE."=". ($page + 1);

		if($ui->checkRequest(UIConst::UI_REQUEST_SORT)){
			$href .= "&".UIConst::UI_REQUEST_SORT."=".$ui->getRequest(UIConst::UI_REQUEST_SORT);
			$href .= "&".UIConst::UI_REQUEST_COL."=".$ui->getRequest(UIConst::UI_REQUEST_COL);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_NUMBER)){
			$href .= "&".UIConst::UI_REQUEST_NUMBER."=".$ui->getRequest(UIConst::UI_REQUEST_NUMBER);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_SEARCH)){
			$href .= "&".UIConst::UI_REQUEST_SEARCH."=".$ui->getRequest(UIConst::UI_REQUEST_SEARCH);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_FILTER)){
			$href .= "&".UIConst::UI_REQUEST_FILTER."=".$ui->getRequest(UIConst::UI_REQUEST_FILTER);
			$href .= "&".UIConst::UI_REQUEST_FILTER_COL."=".$ui->getRequest(UIConst::UI_REQUEST_FILTER_COL);
		}
		$this->getDefaultParameters($href);
		return $href;
	}
	public function generateSortingHref($col_org,$ui){
		if(!$ui->checkRequest(UIConst::UI_REQUEST_SORT)){
			$href = $_SERVER['PHP_SELF']."?".UIConst::UI_REQUEST_SORT."=".TableUI::SORT_DESC;
		}else{
			if($ui->getRequest(UIConst::UI_REQUEST_SORT) == TableUI::SORT_ASC){
				$href = $_SERVER['PHP_SELF']."?".UIConst::UI_REQUEST_SORT."=".TableUI::SORT_DESC;
			}
			else{
				$href = $_SERVER['PHP_SELF']."?".UIConst::UI_REQUEST_SORT."=".TableUI::SORT_ASC;
			}
		}
		$href .= "&".UIConst::UI_REQUEST_COL."=".$col_org[UIConst::UI_NAME];
		if($ui->checkRequest(UIConst::UI_REQUEST_NUMBER)){
			$href .= "&".UIConst::UI_REQUEST_NUMBER."=".$ui->getRequest(UIConst::UI_REQUEST_NUMBER);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_SEARCH)){
			$href .= "&".UIConst::UI_REQUEST_SEARCH."=".$ui->getRequest(UIConst::UI_REQUEST_SEARCH);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_FILTER)){
			$href .= "&".UIConst::UI_REQUEST_FILTER."=".$ui->getRequest(UIConst::UI_REQUEST_FILTER);
			$href .= "&".UIConst::UI_REQUEST_FILTER_COL."=".$ui->getRequest(UIConst::UI_REQUEST_FILTER_COL);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_PAGE)){
			$href .= "&".UIConst::UI_REQUEST_PAGE."=".$ui->getRequest(UIConst::UI_REQUEST_PAGE);
		}
		$this->getDefaultParameters($href);
		return $href;
	}
	public function generateSearchField($ui){
		$field = $ui->style->getInputHTML(FormUI::FORM_FIELD_HIDDEN);
		if($ui->checkRequest(UIConst::UI_REQUEST_SEARCH)){
			$field = str_replace('{value}',$ui->getRequest(UIConst::UI_REQUEST_SEARCH),$field);
		}else{
			$field = str_replace('{value}','1',$field);
		}
		$field = str_replace('{name}',UIConst::UI_REQUEST_SEARCH,$field);
		if($ui->checkRequest(UIConst::UI_REQUEST_SORT)){
			$field .= $ui->style->getInputHTML(FormUI::FORM_FIELD_HIDDEN);
			$field = str_replace('{value}',$ui->getRequest(UIConst::UI_REQUEST_SORT),$field);
			$field = str_replace('{name}',UIConst::UI_REQUEST_SORT,$field);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_NUMBER)){
			$field .= $ui->style->getInputHTML(FormUI::FORM_FIELD_HIDDEN);
			$field = str_replace('{value}',$ui->getRequest(UIConst::UI_REQUEST_NUMBER),$field);
			$field = str_replace('{name}',UIConst::UI_REQUEST_NUMBER,$field);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_FILTER)){
			$field .= $ui->style->getInputHTML(FormUI::FORM_FIELD_HIDDEN);
			$field = str_replace('{value}',$ui->getRequest(UIConst::UI_REQUEST_FILTER),$field);
			$field = str_replace('{name}',UIConst::UI_REQUEST_FILTER,$field);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_PAGE)){
			$field .= $ui->style->getInputHTML(FormUI::FORM_FIELD_HIDDEN);
			$field = str_replace('{value}',$ui->getRequest(UIConst::UI_REQUEST_PAGE),$field);
			$field = str_replace('{name}',UIConst::UI_REQUEST_PAGE,$field);
		}
		if(!empty($this->defaultURLParams)){
			foreach($this->defaultURLParams as $name => $value)
			$field .= $ui->style->getInputHTML(FormUI::FORM_FIELD_HIDDEN);
			$field = str_replace('{value}',$value,$field);
			$field = str_replace('{name}',$name,$field);;
		}
		return $field;
	}
	public function getFileDeleteURL($item,$obj){
		$href = $_SERVER['PHP_SELF']."?".UIConst::UI_FORM_TASK_NAME."=".$_REQUEST[UIConst::UI_FORM_TASK_NAME];
		$href .= "&".UI::UI_REQUEST_ID."=".$_REQUEST[UI::UI_REQUEST_ID];
		$href .= "&".UIConst::UI_FORM_ITEM_ID."=".$_REQUEST[UIConst::UI_FORM_ITEM_ID];
		$href .= "&".UIConst::UI_FORM_OBJ_NAME."=".get_class($obj);
		$href .= "&".UIConst::UI_FORM_DELETE_FILE."=".$item;
		return $href;
	}
	public function getDefaultParameters(&$href){
		if(!empty($this->defaultURLParams)){
			foreach($this->defaultURLParams as $name => $value)
			$href .= "&$name=".$value;
		}
	}
}