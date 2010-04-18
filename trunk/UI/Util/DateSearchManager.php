<?php

require_once dirname(__FILE__).'/SearchManager.php';
class DateSearchManager implements SearchManager {
	public function processSearch($key,$name_prefix){
		$start = $_REQUEST[$name_prefix."start"];
		$end = $_REQUEST[$name_prefix."end"];
		$i=0;
		if(!empty($start))
		$where[$key][$i++] = array($start,Model::MODEL_WHERE_TYPE_GTEQ,Model::MODEL_WHERE_TYPE_AND);
		if(!empty($end))
		$where[$key][$i++] = array($end,Model::MODEL_WHERE_TYPE_LTWQ,Model::MODEL_WHERE_TYPE_AND);
		return $where;
	}
	public function getInputTypeHTML($name_prefix){
		$html = "
				<td>
					From - <input id='start' type='text' name='{$name_prefix}start' value='".$_REQUEST[$name_prefix."start"]."' {prop}/>
					To - <input id='end' type='text' name='{$name_prefix}end' value='".$_REQUEST[$name_prefix."end"]."' {prop}/>
				</td>
			";
		return $html;
	}
}