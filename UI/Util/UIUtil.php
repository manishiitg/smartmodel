<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */
class UIUtil{
	public static function getLimit($enablePagaing,&$page,&$pageSize,$ui){
		$limit = "";
		if($enablePagaing){
			if($ui->checkRequest(UIConst::UI_REQUEST_PAGE)){
				$page =$ui->getRequest(UIConst::UI_REQUEST_PAGE);
			}
			if($ui->checkRequest(UIConst::UI_REQUEST_NUMBER)){
				$pageSize =$ui->getRequest(UIConst::UI_REQUEST_NUMBER);
			}
			$start = $page * $pageSize;
			$end = $pageSize;
			$limit = "LIMIT $start,$end";
		}
		return $limit;
	}

	public static function getOrderBy(&$sorting,&$sortCol,$ui){
		$orderby = null;
		if($ui->checkRequest(UIConst::UI_REQUEST_SORT)){
			$s = "";
			if($ui->getRequest(UIConst::UI_REQUEST_SORT) == TableUI::SORT_ASC){
				$s = UIConst::UI_ASC;
				$sorting = TableUI::SORT_ASC;
			}else{
				$s = UIConst::UI_DESC;
				$sorting = TableUI::SORT_DESC;
			}
			$sortCol =$ui->getRequest(UIConst::UI_REQUEST_COL);
			$orderby = array($s=>$ui->getRequest(UIConst::UI_REQUEST_COL));
		}else{
			if(empty($sorting)){
				$sorting = TableUI::SORT_ASC;
			}
			if($sorting == TableUI::SORT_ASC){
				$s = UIConst::UI_ASC;
			}else{
				$s = UIConst::UI_DESC;
			}
			if(!empty($sortCol)){
				$orderby = array($s=>$sortCol);
			}else{
				$orderby = array();
			}
		}
		return $orderby;
	}
	public static function getWhere($ui){
		$where = null;
		if($ui->checkRequest(UIConst::UI_REQUEST_FILTER)){
			$filterCol = $ui->getRequest(UIConst::UI_REQUEST_FILTER_COL);
			$filter = $ui->getRequest(UIConst::UI_REQUEST_FILTER);
			if($filter != UIConst::UI_NONE){
				$where[$filterCol] = $filter;
			}
			print_r($where);
		}
		if($ui->checkRequest(UIConst::UI_REQUEST_SEARCH)){
			if($ui->checkRequest($ui->id.'_'.UIConst::UI_SEARCH_GENERIC_FIELD) && !empty($_REQUEST[$ui->id.'_'.UIConst::UI_SEARCH_GENERIC_FIELD])){
				foreach($ui->obj->getKeys() as $key){
					$val = $ui->getRequest($ui->id.'_'.UIConst::UI_SEARCH_GENERIC_FIELD);
					if(!empty($val))
					$where[$key] = array('%'.$val.'%',Model::MODEL_WHERE_TYPE_LIKE,Model::MODEL_WHERE_TYPE_OR);
				}
			}else{
				$where = array();
				foreach($ui->obj->getKeys() as $key){
					if($ui->addCustomSearchManager[$key]){
						$class = $ui->addCustomSearchManager[$key];
						$obj_mgr = new $class;
						if(UIUtil::existsFunction($class.'.processSearch')){
							$array = UIUtil::callFunction($class.'.processSearch',$key,$ui->id.'_');
							if(is_array($array)){
								if(!empty($where))
								$where = array_merge($where,$array);
								else
								$where = $array;
							}
						}
					}else{
						if($ui->checkRequest($ui->id.'_'.$key)){
							$val = $ui->getRequest($ui->id.'_'.$key);
							if(!empty($val)){
								$where[$key] = array('%'.$val.'%',Model::MODEL_WHERE_TYPE_LIKE,Model::MODEL_WHERE_TYPE_OR);
							}
						}
					}
				}
			}
		}

		return $where;
	}
	public static function generateUniqID(&$ui){
		$str = "";
		$obj_vars = get_object_vars($ui);
		foreach($obj_vars as $k => $v){
			if(is_array($v) || is_object($v))
			$str .= print_r($v,true);
			else
			$str .= $v;
			$str .= $k;
		}
		$str = md5($str);
		if(strlen($str) > 8){
			$str = substr($str,0,8);
		}
		$ui->id = $str;

		$ui->setDefaultURLParams(array(UI::UI_REQUEST_ID=>$ui->id));
	}
	public static function checkMessages($result,$style){
		if( isset($_REQUEST[UIConst::UI_ERROR_MESSAGE]) ){
			$error_message = $_REQUEST[UIConst::UI_ERROR_MESSAGE];
		}
		if( isset($_REQUEST[UIConst::UI_SUCESS_MESSAGE]) ){
			$sucess_message = $_REQUEST[UIConst::UI_SUCESS_MESSAGE];
		}
		if( isset($result[UIConst::UI_ERROR_MESSAGE]) ){
			$error_message = $result[UIConst::UI_ERROR_MESSAGE];
		}
		if( isset($result[UIConst::UI_SUCESS_MESSAGE]) ){
			$sucess_message = $result[UIConst::UI_SUCESS_MESSAGE];
		}
		$html = "";
		if(isset($error_message)){
			$html .= $style->getErrorMessage($error_message);
		}
		if(isset($sucess_message)){
			$html .= $style->getSucessMessage($sucess_message);
		}
		return $html;
	}

	public static function callFunction(){
		$args = func_get_args();
		if(sizeof($args) > 0){
			$func = $args[0];
			if(strpos($func,'.') === false){
				if(function_exists($func)){
					array_shift($args);
					return call_user_func_array($func,$args);
				}
			}else{
				$func = $args[0];
				$class = substr($func,0,strpos($func,'.'));
				$func = substr($func,strpos($func,'.')+1);
				if(method_exists($class,$func)){
					$obj = new $class;
					array_shift($args);
					return call_user_func_array(array($obj,$func),$args);
				}
			}
		}
	}
	public static function existsFunction(){
		$args = func_get_args();
		if(sizeof($args) > 0){
			$func = $args[0];
			if(strpos($func,'.') === false){
				return function_exists($func);
			}else{
				$func = $args[0];
				$class = substr($func,0,strpos($func,'.'));
				$func = substr($func,strpos($func,'.')+1);
				return method_exists($class,$func);
			}
		}
	}
	public static function getClassVar($cols){
		$table = substr($cols,0,strpos($cols,'.'));
		$var = substr($cols,strpos($cols,'.')+1);
		$array[0] = $table;
		$array[1] = $var;
		$array[UIConst::UI_TABLE] = $table;
		$array[UIConst::UI_VAR] = $var;
		return $array;
	}
}