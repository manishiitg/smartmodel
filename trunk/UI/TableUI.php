<?php


class TableUI {

	const SORT_ASC = 1;
	const SORT_DESC = 2;

	public $pageSize = 10;
	public $page = 0;
	public $enablePagaing = true;
	public $enableSorting = true;
	public $enableFiltering = true;
	public $enableSearching = true;
	public $enableUtilCSV = true;
	public $enableUtilPDF = false;
	public $enableGenericSearchField = true;
	public $sortCol;
	public $sorting;

	/** New Added	 */
	public $enableCheckboxes = false;
	public $enableBulkOperation = false;

	/**
	 * UI Related
	 */
	public $actionColoumText = "Action";

	public $id = 0;
	public function __construct($obj,$design){
		$this->hrefUtil = new HrefUtil();
		$this->obj = $obj;
		require_once dirname(__FILE__)."/Styles/{$design}/{$design}.php";
		require_once dirname(__FILE__)."/Styles/{$design}/{$design}_Table.php";
		$t = $design."_Table";
		$this->style = new $t;
		$this->baseStyle = new $design;
	}


	private function getData(&$data,&$columnName){
		$obj = $this->obj;
		$where = UIUtil::getWhere($this); //Filter Columns
		if($this->enablePagaing){
			$limit = UIUtil::getLimit($this->enablePagaing,$this->page,$this->pageSize,$this);
			if(empty($this->addModel)){
				$this->totalPages = $obj->count($where);
			}else{
				$obj_vars = get_object_vars($obj);
				$array_check = $obj->getKeys($obj->_fields);
				foreach($obj_vars as $o=>$v){
					if(isset($v) && in_array($o, $array_check) && (!empty($v) || $v ==0) ){
						$where[$o] = $v;
					}
				}
				$this->totalPages = $obj->countJoin($this->addModel,$where);
			}
		}else{
			$limit = "";
		}
		$orderby = UIUtil::getOrderBy($this->sorting,$this->sortCol,$this);
		if(empty($this->addModel)){

			$data = $obj->read(null,$where,$orderby,$limit);
			$columnName = $this->getColumnName($obj);
		}else{
			if(!empty($where)){
				$where = array_merge($where,$this->customWhere);
			}else{
				$where = $this->customWhere;
			}
			$data = $obj->join($this->addModel,$where,null,$orderby,$limit);
			$columnName = $this->getColumnName($obj);
			foreach($this->addModel as $k => $v){
				if(strpos($k,'.') !== false){
					$class = UIUtil::getClassVar($k);
					if($class[UIConst::UI_TABLE] != get_class($obj)){
						$obj2 = new $class[UIConst::UI_TABLE];
						$columnName2 = $this->getColumnName($obj2);
						$columnName = array_merge($columnName,$columnName2);
					}
				}
				if(is_array($v)){
					$v = $v[0];
				}
				if(strpos($v,'.') !== false){
					$class = UIUtil::getClassVar($v);
					if($class[UIConst::UI_TABLE] != get_class($obj)){
						$obj2 = new $class[UIConst::UI_TABLE];
						$columnName2 = $this->getColumnName($obj2);
						$columnName = array_merge($columnName,$columnName2);
					}
				}
			}
		}
		if(!empty($this->customColumn)){
			foreach($this->customColumn as $col => $func){
				$c = array();
				$c[UIConst::UI_NAME] = $col;
				$c[UIConst::UI_COLNAME] = $col;
				$columnName[] = $c;
			}
		}
		if(!empty($this->orderColumn)){
			$columnNameOrdered = array();
			$addedNames = array();
			foreach($columnName as $cols){
				if(isset($this->orderColumn[$cols[UIConst::UI_NAME]])){
					$i = $this->orderColumn[$cols[UIConst::UI_NAME]];
					$columnNameOrdered[$i-1] = $cols;
					$addedNames[] = $cols[UIConst::UI_NAME];
				}
			}
			$i=0;
			foreach($columnName as $cols){
				if(!in_array($cols[UIConst::UI_NAME],$addedNames)){
					while(isset($columnNameOrdered[$i])){
						$i++;
					}
					$columnNameOrdered[$i] = $cols;

				}
			}
			$columnName = $columnNameOrdered;

			$keys = array_keys($columnName);
			sort($keys);
			$i = 0;
			foreach($keys as $key){
				if(isset($columnName[$key][UIConst::UI_NAME]))
				$columnNameOrdered[$i++] = $columnName[$key];
			}
			$columnName = $columnNameOrdered;
		}
		if(!empty($this->rowFilter)){
			if(UIUtil::existsFunction($this->rowFilter)){
				$func = $this->rowFilter;
				$data_new = array();
				foreach($data as $row){
					if(UIUtil::callFunction($func,$row,$data)){
						array_push($data_new,$row);
					}
				}
			}
			$data = $data_new;
			unset($data_new);
		}
	}
	public function generateTable($sortImg = true,$result = null){
		UIUtil::generateUniqID($this);
		$obj = $this->obj;
		$paging = "";
		$filter = "";
		$util = "";
		$advanced = "";
		$table_header = "";
		$table_content = "";
		$bulk_content = "";

		$data = "";
		$columnName = "";

		$this->getData($data,$columnName);

		if($this->enablePagaing){
			$paging = $this->generatePagingUI();
		}
		$table_header = $this->generateHeaderUI($columnName,$sortImg);
		$table_content = $this->generateContentUI($data,$columnName);

		if($this->enableFiltering){
			$filter = $this->generateFilterUI($obj);
		}
		if($this->enableSearching){
			$advanced = $this->generateSearchUI();
		}
		$util = $this->generateUtil();
		if($this->enableBulkOperation)
		$bulk_content = $this->getBulkContentUI();

		$table = $this->style->getTableHTMLStructure();
		$table = str_replace('{UTIL_SECTION}',$util,$table);
		$table = str_replace('{BULK_SECTION}',$bulk_content,$table);
		$table = str_replace('{PAGING_FORM}',$paging,$table);
		$table = str_replace('{FILTER_FIELDS}',$filter,$table);
		$table = str_replace('{ADVANCED_SEARCH}',$advanced,$table);
		$table = str_replace('{TABLE_HEADER}',$table_header,$table);
		$table = str_replace('{TABLE_CONTENT}',$table_content,$table);

		$res = UIUtil::checkMessages($result,$this->baseStyle);
		$table = str_replace("{MESSAGES}",$res,$table);

		$table = $this->style->loadScripts().$table;

		return $table;
	}
	private function generateSearchUI(){
		$cols = UIConst::UI_SEARCH_COL_NUM;
		$html = "";

		if($this->enableGenericSearchField){
			$this->searchField[] = UIConst::UI_SEARCH_GENERIC_FIELD;
			$this->columnNameMap[UIConst::UI_SEARCH_GENERIC_FIELD] = UIConst::UI_SEARCH_GENERIC_NAME;
		}
		if(sizeof($this->searchField) > 0){

			$colspan = 0;
			$size = sizeof($this->searchField);
			if($size%$cols != 0 ){
				$y = floor($size/$cols);
				$colspan = ($y+1) * $cols - $size;

			}

			$html = $this->style->getFormStructure();
			$col = 1;
			$row = 1;
			$field = "";
			$wrap = "";
			$i = 0;
			foreach($this->searchField as $f){
				if(isset($this->columnNameMap[$f])){
					$col_name =$this->columnNameMap[$f];
				}else{
					$col_name = $this->obj->getColumnName($f);
				}
				if(isset($this->addCustomSearchManager[$f])){
					$class = $this->addCustomSearchManager[$f];
					$obj_mgr = new $class;
					$field .= $this->style->getFormLabelHTML($col_name,$row,$col).$obj_mgr->getInputTypeHTML($this->id .'_');
				}else{
					$field .= $this->style->getFormLabelHTML($col_name,$row,$col).$this->style->getInputHTML(FormUI::FORM_FIELD_TEXTFIELD);
					$value = "";
					if($this->checkRequest($this->id .'_'. $f)){
						$value = $this->getRequest($this->id .'_'. $f);
					}
					$field = str_replace('{value}',$value,$field);
					$field = str_replace('{name}',$this->id .'_'. $f,$field);
				}
				if($col % ($cols) == 0 || ($i == sizeof($this->searchField) - 1) ){
					$wrap .= $this->style->getFormFieldWrapper($row,$col);
					$wrap = str_replace('{field}',$field,$wrap);
					$field = "";
					$col=1;
					$row++;
				}else{
					$col++;
				}
				$i++;
			}
			$html = str_replace('{FORM_FIELDS}',$wrap,$html);
		}
		$html = str_replace('{HIDDEN_TYPE}',$this->hrefUtil->generateSearchField($this),$html);
		return $html;
	}
	private function getBulkContentUI(){
		$bulkHTML = $this->style->getBulkHTML();
		$buttons = array(UIConst::UI_BULK_BUTTON_DELETE,UIConst::UI_BULK_BUTTON_EDIT,UIConst::UI_BULK_BUTTON_SELECT_ALL,UIConst::UI_BULK_BUTTON_SELECT_NONE,UIConst::UI_BULK_BUTTON_ADDNEW);

		if(!empty($this->addFormButton)){
			foreach($this->addFormButton as $k => $v){
				array_push($buttons,$k);
			}
		}
		foreach($buttons as $button){
			if(isset($this->formButton[$button])){
				$val = $this->formButton[$button];
				if(is_bool($val) && !$val){
					$bulkHTML = str_replace('{'.$button.'}','',$bulkHTML);
					continue;
				}else{
					if(UIUtil::existsFunction($val)){
						if(!UIUtil::callFunction($val)){
							$bulkHTML = str_replace('{'.$button.'}','',$bulkHTML);
							continue;
						}
					}
				}
			}
			$button_html = $this->style->getBulkHTMLButton($button);
			$href = '#';
			if(isset($this->addFormButton[$button])){
				$href = $this->addFormButton[$button];
				if(UIUtil::existsFunction($href)){
					$id = str_replace(" ","_",$button);
					$button_html .= UIUtil::callFunction($href,$id);
					$href = "#";
				}
			}else if($button == UIConst::UI_BULK_BUTTON_SELECT_ALL){
				$button_html .= $this->style->selectAllScript();
			}else if($button == UIConst::UI_BULK_BUTTON_SELECT_NONE){
				$button_html .= $this->style->selectNoneScript();
			}else if($button == UIConst::UI_BULK_BUTTON_DELETE){
				$url = UIConst::UI_FORM_TASK_NAME.'='.UIConst::UI_FORM_TASK_DELETE.'&'.UIConst::UI_FORM_OBJ_NAME.'='.get_class($this->obj);
				$this->hrefUtil->getDefaultParameters($url);
				$button_html .= $this->style->selectDeleteScript($url);
			}else if($button == UIConst::UI_BULK_BUTTON_EDIT){
				$url = UIConst::UI_FORM_TASK_NAME.'='.UIConst::UI_FORM_TASK_EDIT;
				$this->hrefUtil->getDefaultParameters($url);
				$button_html .= $this->style->selectEditScript($url);
			}else if($button == UIConst::UI_BULK_BUTTON_ADDNEW){
				$button_html .= $this->style->selectAddNewScript($url);
				$href =  $_SERVER['PHP_SELF']."?" . UIConst::UI_FORM_TASK_NAME.'='.UIConst::UI_FORM_TASK_INSERT;
				$this->hrefUtil->getDefaultParameters($href);
			}
			$button_html = str_replace('{href}',$href,$button_html);
			$button_html = str_replace('{id}',str_replace(' ','_',$button),$button_html);

			if(strpos($bulkHTML,'{'.$button.'}') !== false){
				$bulkHTML = str_replace('{'.$button.'}',$button_html,$bulkHTML);
			}else{
				$bulkHTML .= $button_html;
			}
		}
		return $bulkHTML;
	}
	public function generate(){
		$exports = $this->exportOperations;
		if($this->enableUtilCSV){
			$exports[UIConst::UI_EXPORT_CSV_NAME] = 'ExportUtil.generateCSV';
		}
		if($this->enableUtilPDF){
			$exports[UIConst::UI_EXPORT_PDF_NAME] = 'ExportUtil.generatePDF';
		}
		$this->exportOperations = $exports;
		if(isset($_REQUEST[UIConst::UI_EXPORT_REQUEST])){
			UIUtil::generateUniqID($this);
			$obj = $this->obj;
			foreach($exports as $key => $val){
				$name = str_replace(' ','_',$key);
				if($name == $_REQUEST[UIConst::UI_EXPORT_REQUEST]){
					$cols = "";
					$data = "";
					$this->getData($data,$cols);
					$finalCols = array();
					$finalData = array();
					for($i=0;$i<sizeof($cols);$i++){
						$col_org = $cols[$i];
						if(in_array($col_org[UIConst::UI_NAME],array_keys($this->columnNameMap))){
							$col_org[UIConst::UI_MAPNAME] = $this->columnNameMap[$col_org[UIConst::UI_NAME]];
						}else{
							$col_org[UIConst::UI_MAPNAME] = $col_org[UIConst::UI_COLNAME];
						}
						$finalCols[] = $col_org[UIConst::UI_MAPNAME];
					}
					$k = 0;
					if(isset($data) && !empty($data)){
						foreach($data as $row){
							$newrow = array();
							for($i=0;$i<sizeof($cols);$i++){
								$res = "";
								$c = $cols[$i];
								if(strpos($c[UIConst::UI_NAME],'.')!==false){
									$cl = UIUtil::getClassVar($c[UIConst::UI_NAME]);
									$c[UIConst::UI_COLNAME] = $cl[UIConst::UI_TABLE].'.'.$c[UIConst::UI_COLNAME];
								}
								if(isset($row[$c[UIConst::UI_COLNAME]])){
									$res = $row[$c[UIConst::UI_COLNAME]];
									if(isset($this->columnMapping[$c[UIConst::UI_NAME]])){
										$func = $this->columnMapping[$c[UIConst::UI_NAME]];
										if(UIUtil::existsFunction($func)){
											$res = UIUtil::callFunction($func,$res,$row);
										}
									}
								}else if(in_array($c[UIConst::UI_COLNAME],array_keys($this->customColumn))){
									$func = $this->customColumn[$c[UIConst::UI_COLNAME]];
									if(UIUtil::existsFunction($func)){
										$res = UIUtil::callFunction($func,$row);
									}
								}
								if(is_object($res)){
									$res = $res."";
									$res = str_replace('<br>','-',$res);
								}else if(is_array($res)){
									$res = implode("-",$res);
								}
								if(in_array($c[UIConst::UI_COLNAME],array_keys($this->columnNameMap))){
									$c[UIConst::UI_COLNAME] = $this->columnNameMap[$c[UIConst::UI_COLNAME]];
								}
								if(strpos($c[UIConst::UI_NAME],'.')!==false){
									$cl = UIUtil::getClassVar($c[UIConst::UI_NAME]);
									$c[UIConst::UI_COLNAME] = $cl[UIConst::UI_VAR];
								}

								$newrow[$c[UIConst::UI_COLNAME]] = $res;
							}
							$finalData[$k++] = $newrow;
						}
					}
					UIUtil::callFunction($val,$finalCols,$finalData);
					die();
				}

			}
		}
	}
	private function generateUtil(){

		$exports = $this->exportOperations;
		$html = "";

		foreach($exports as $key => $val){
			$html .= $this->style->getUtilHTML($key);
			$name = str_replace(' ',"_",$key);
			$href = $this->hrefUtil->generateExportHref($this->obj,$name);
			$html = str_replace('{href}',$href,$html);
		}
		return $html;
	}


	private function generateFilterUI($obj){
		$html2 = $this->style->getFilterHTML();
		$html = "";
		$i = 0;
		$main_html = "";
		if(sizeof($this->filterColumn) > 0){
			foreach($this->filterColumn as $col){
				$array = array();

				if(strpos($col,'.')!== false){
					$cl = UIUtil::getClassVar($col);
					$obj_r = new $cl[UIConst::UI_TABLE];
					$var = $cl[UIConst::UI_VAR];
					$db_col =  $obj_r->getColumnName($var);
					$select = array($db_col);
					$data = $obj_r->read($select);
					foreach($data as $row){;
					$array[] = $row[$db_col];
					}
				}else{
					$db_col =  $obj->getColumnName($col);
					$select = array($db_col);
					$data = $obj->read($select);
					if(sizeof($data) > 0)
					foreach($data as $row){
						$array[] = $row[$db_col];
					}
				}
				$array = array_unique($array);
				if(!empty($this->columnMapping) && isset($this->columnMapping[$col])){
					$func = $this->columnMapping[$col];
					$array_new = array();
					if(UIUtil::existsFunction($func)){
						foreach($array as $k => $v){
							$a = UIUtil::callFunction($func,$v);
							$array_new[$v] = $a;
						}
						$array = $array_new;
					}
				}
				if(!empty($this->columnNameMap) && isset($this->columnNameMap[$col])){
					$col_name = $this->columnNameMap[$col];
				}else{
					$col_name = $obj->getColumnName($col);
				}
				$html3 = $this->style->getFilterBox();
				$html3 = str_replace('{FILTER_NAME}',$col_name,$html3);

				$html = '';
				$html .= "<form style='display:inline' method='get' action='{$_SERVER['PHP_SELF']}' id='filter-form$i' >";
				if(TableUI::checkRequest(UIConst::UI_REQUEST_SORT)){
					$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_SORT."' value='". $this->getRequest(UIConst::UI_REQUEST_SORT) ."' />";
					$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_COL."' value='". $this->getRequest(UIConst::UI_REQUEST_COL) ."' />";
				}
				if(TableUI::checkRequest(UIConst::UI_REQUEST_PAGE)){
					$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_PAGE."' value='". $this->getRequest(UIConst::UI_REQUEST_PAGE) ."' />";
				}
				if(TableUI::checkRequest(UIConst::UI_REQUEST_NUMBER)){
					$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_NUMBER."' value='". $this->getRequest(UIConst::UI_REQUEST_NUMBER) ."' />";
				}
				if(!empty($this->hrefUtil->defaultURLParams)){
					foreach($this->hrefUtil->defaultURLParams as $name => $value){
						$html .= "<input type='hidden' name='$name' value='$value' />";
					}
				}


				$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_FILTER_COL."' value='{$col}' />";
				$html .= "<input type='hidden' id='".UIConst::UI_REQUEST_FILTER."' name='".UIConst::UI_REQUEST_FILTER."' value='".UIConst::UI_NONE."' />";
				$html .= '<select onchange="jQuery(\'#'.UIConst::UI_REQUEST_FILTER.'\').val(this.options[this.selectedIndex].value);jQuery(\'#filter-form'.$i.'\').submit();">';

				$found = false;
				foreach($array as $k => $v){
					$html .= "<option value='$k'";
					if(TableUI::checkRequest(UIConst::UI_REQUEST_FILTER) && ($this->getRequest(UIConst::UI_REQUEST_FILTER_COL) == $col) && ($this->getRequest(UIConst::UI_REQUEST_FILTER) == $k)){
						$html .= ' selected ';
						$found = true;
					}
					$html .= ">$v</option>";
				}
				if($found)
				$html .= "<option value='".UIConst::UI_NONE."'>".UIConst::UI_NONE."</option>";
				else
				$html .= "<option value='".UIConst::UI_NONE."' selected>".UIConst::UI_NONE."</option>";

				$html .= "</select>";
				$html .= "</form>";

				$html3 = str_replace('{FILTER_FORM}',$html,$html3);
				$main_html .= $html3;
				$i++;
			}
		}
		$html2 = str_replace('{FILTER_FORMS}',$main_html,$html2);
		return $html2;
	}

	private function generateContentUI($data,$cols){
		$html = "";
		$k = 0;
		if(isset($data) && !empty($data)){
			foreach($data as $row){
				$html .= $this->style->getContentRowHTML($k);
				$j = 0;
				$row_html = "";
				/**
				 * If check are enabled, then these are generated here based on user functions
				 */
				if($this->enableCheckboxes){
					$name = "";
					if(empty($this->checkBoxProp) ||  (!isset($this->checkBoxProp['id'])) ){
						/** Default If Userd Didnt set any custom properties */

					}else{
						/** IF User Has Set Sum custom id for the checkbox */
						$text = $this->checkBoxProp['id'];
						$id = $text;
						$pattern = "/{[a-zA-Z0-9\.]*}/";
						$matches = array();
						$match = preg_match_all($pattern,$text,$matches);
						if($match > 0){
							foreach($matches[0] as $replace){
								$replace3 = str_replace('{','',$replace);
								$replace3 = str_replace('}','',$replace3);
								if(strpos($replace3,'.') != false){
									$replace2 = UIUtil::getClassVar($replace3);
									$class = $replace2[UIConst::UI_TABLE];
									$var = $replace2[UIConst::UI_VAR];
									$obj_n = new $class;
									$col = $obj_n->getColumnName($var);
									$col = $obj_n->_table . '.' . $col;
								}else{
									$col = $this->obj->getColumnName($replace3);
								}
								$id = str_replace($replace,$row[$col],$name);
							}
						}

					}
					$keyCol = $this->obj->getModelKeyCol();
					$keyCol = $this->obj->getColumnName($keyCol);
					$name  = $row[$keyCol];
					/** Name Of CheckBox will always be model's unique key **/

					if(!isset($id) || empty($id)){$id = $name;}
					$res = $this->style->getCheckBoxHTML($j);
					$res = str_replace('{id}',$id,$res);
					$res = str_replace('{name}',$name,$res);
					$cell = $this->style->getContentCellHTML(true,false,0);
					$cell = str_replace('{CELL}',$res,$cell);
					$row_html .= $cell;
				}
				/** Checkbox processing end here and data processing starts **/
				for($i=0;$i<sizeof($cols);$i++){
					$res = "";
					if($this->enableCheckboxes){
						$first = ($j == 0) ? true : false;
					}else{
						$first = false;
					}
					if(empty($this->actionColumn))
					$last = ($j == sizeof($cols) - 1) ? true : false;
					else
					$last = false;
					// If enabled check is true, the first column is the checkbox column, so pass +1 to getHeaderRow function
					$m = $this->enableCheckboxes ? $j + 1 : $j;
					$cell = $this->style->getContentCellHTML($first,$last,$m);
					$c = $cols[$i];
					if(strpos($c[UIConst::UI_NAME],'.')!==false){
						$cl = UIUtil::getClassVar($c[UIConst::UI_NAME]);
						$c[UIConst::UI_COLNAME] = $cl[UIConst::UI_TABLE].'.'.$c[UIConst::UI_COLNAME];
					}
					if(isset($row[$c[UIConst::UI_COLNAME]])){
						$res = $row[$c[UIConst::UI_COLNAME]];
						if(isset($this->columnMapping[$c[UIConst::UI_NAME]])){
							$func = $this->columnMapping[$c[UIConst::UI_NAME]];
							if(UIUtil::existsFunction($func)){
								$res = UIUtil::callFunction($func,$res,$row);
							}
						}
					}else if(in_array($c[UIConst::UI_COLNAME],array_keys($this->customColumn))){
						$func = $this->customColumn[$c[UIConst::UI_COLNAME]];
						if(UIUtil::existsFunction($func)){
							$res = UIUtil::callFunction($func,$row);
						}
					}
					if(!isset($res) ||  empty($res)){
						$res = UIConst::UI_EMPTY_CELL;
					}
					$cell = str_replace('{CELL}',$res,$cell);
					$row_html .= $cell;
					$j++;
				}
				if(!empty($this->actionColumn)){

					$cell = $this->style->getContentCellHTML(false,false,$j,true);
					$action_html = '';
					foreach($this->actionColumn as $name => $attr){

						$show = true;
						if(isset($attr[UIConst::UI_FUNC])){
							$func = $attr[UIConst::UI_FUNC];
							if(UIUtil::existsFunction($func)){
								$show = UIUtil::callFunction($func,$row);
							}
						}
						if($show){
							$href = $this->style->getContentActionLink();
							$href = str_replace('{href}',$attr[UIConst::UI_HREF],$href);
							$href = str_replace('{name}',$name,$href);

							foreach($row as $col => $val){
								if(strpos($col,'.') === false){
									$col = $this->obj->getFieldName($col);
									$href = str_replace("{".$col."}",$val,$href);
								}else{
									$cl = UIUtil::getClassVar($col);
									$obj_r = new $cl[UIConst::UI_TABLE];
									$var = $cl[UIConst::UI_VAR];
									$var = $obj_r->getFieldName($var);
									$href = str_replace("{".$cl[UIConst::UI_TABLE].'.'.$var."}",$val,$href);
								}
							}

							$action_html .= $href;
						}
					}
					$cell = str_replace('{CELL}',$action_html,$cell);
					$row_html .= $cell;
				}
				$html = str_replace('{ROW}',$row_html,$html);
				$k++;
			}
		}else{
			if(!empty($this->emptyTableHTML)){
				$html = $this->style->getContentRowHTML(0);
				$html = str_replace('{ROW}',$this->emptyTableHTML,$html);
			}else if(!empty($this->emptyTableMessage)){
				$html = $this->style->getContentRowHTML(0);
				$cols_size = sizeof($cols);
				if($this->enableCheckboxes)
				$cols_size++;
				if(!empty($this->actionColumn)){
					$cols_size++;
				}
				$html = str_replace('{ROW}',"<td colspan=$cols_size>".$this->emptyTableMessage."</td>",$html);
			}
		}
		return $html;
	}

	private function generateHeaderUI($cols,$sortImg){
		$html = "";

		if($this->enableCheckboxes){
			$text = "";
			if(!empty($this->checkBoxProp)){
				$text =  $this->checkBoxProp[UIConst::UI_COLNAME];
			}
			$html .= $this->style->getHeaderRow($text,true,false,0);
		}
		for($i=0;$i<sizeof($cols);$i++){
			/**
			 * Here, it is checked if this colum is first or last and
			 * varibles $first and $last and set accordingly
			 */
			if(!$this->enableCheckboxes){
				$first = ($i == 0) ? true : false;
			}else{
				$first = false;
			}
			if(empty($this->actionColumn)){
				$last = ($i == sizeof($cols)-1) ? true : false;
			}
			else{
				$last = false;
			}

			/**
			 * Here its checked if there is any column name mapping of the specified column
			 */
			$col_org = $cols[$i];
			if(in_array($col_org[UIConst::UI_NAME],array_keys($this->columnNameMap))){
				$col_org[UIConst::UI_MAPNAME] = $this->columnNameMap[$col_org[UIConst::UI_NAME]];
			}else{
				$col_org[UIConst::UI_MAPNAME] = $col_org[UIConst::UI_COLNAME];
			}

			$href = "";
			/**
			 * Default sorting order is checked if it's specified by the user
			 * in $sorting class variable
			 */
			if(!isset($this->sorting) && empty($this->sorting)){
				$sort = self::SORT_ASC;
			}else{
				$sort = $this->sorting;
			}

			/**
			 * If soring is enabled, it will generate the correct link for the column depending on sorting
			 */
			if($this->enableSorting){
				if(TableUI::checkRequest(UIConst::UI_REQUEST_SORT)){
					if($this->getRequest(UIConst::UI_REQUEST_SORT) == self::SORT_ASC){
						$sort = self::SORT_DESC;
					}else{
						$sort = self::SORT_ASC;
					}
				}
				$href = $this->hrefUtil->generateSortingHref($col_org,$this);
			}
			/**
			 * If user has set the perticular column, not to be sorted
			 */
			if(in_array($col_org[UIConst::UI_NAME],$this->removeSorting)){
				// If enabled check is true, the first column is the checkbox column, so pass +1 to getHeaderRow function
				$j = $this->enableCheckboxes ? $i + 1 : $i;
				$html .= $this->style->getHeaderRow($col_org[UIConst::UI_MAPNAME],$first,$last,$j,false,$href,$sort);
			}else{
				if($col_org[UIConst::UI_NAME] == $this->sortCol){
					$show = true;
				}else{
					$show = false;
				}
				if(!$sortImg){
					//This is passed from the generate table function, if this is set as false,
					//the sorting images Up Arraw and Down Arraw won't show
					$show = false;
				}
				// If enabled check is true, the first column is the checkbox column, so pass +1 to getHeaderRow function
				$j = $this->enableCheckboxes ? $i + 1 : $i;
				$html .= $this->style->getHeaderRow($col_org[UIConst::UI_MAPNAME],$first,$last,$j,$this->enableSorting,$href,$sort,$show);

			}
		}
		if(!empty($this->actionColumn)){
			$first = false;
			$last = true;
			// If enabled check is true, the first column is the checkbox column, so pass +1 to getHeaderRow function
			$j = $this->enableCheckboxes ? $i + 1 : $i;
			$html .= $this->style->getHeaderRow($this->actionColoumText,$first,$last,$j);
		}
		return $html;
	}
	private function generatePagingUI(){
		if($this->checkRequest(UIConst::UI_REQUEST_PAGE)){
			$currentPage = $this->getRequest(UIConst::UI_REQUEST_PAGE);
		}else{
			$currentPage = 0;
		}
		$pages = ceil($this->totalPages/$this->pageSize);
		if($pages > $currentPage+1){
			$hasNext = true;
		}else{
			$hasNext = false;
		}
		if($this->page > 0){
			$hasPrev = true;
		}else{
			$hasPrev = false;
		}
		$ui = $this->style->getPagingHTML($hasNext,$hasPrev);
		$html = "";
		$html .= "<form style='display:inline' method='get' action='{$_SERVER['PHP_SELF']}' id='paging-form'>";
		if(TableUI::checkRequest(UIConst::UI_REQUEST_SORT)){
			$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_SORT."' value='".$this->getRequest(UIConst::UI_REQUEST_SORT)."' />";
			$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_COL."' value='".$this->getRequest('col')."' />";
		}
		if(TableUI::checkRequest(UIConst::UI_REQUEST_PAGE)){
			$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_PAGE."' value='".$this->getRequest(UIConst::UI_REQUEST_PAGE)."' />";
		}
		if(TableUI::checkRequest(UIConst::UI_REQUEST_FILTER)){
			$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_FILTER."' value='".$this->getRequest(UIConst::UI_REQUEST_FILTER)."' />";
			$html .= "<input type='hidden' name='".UIConst::UI_REQUEST_FILTER_COL."' value='".$this->getRequest(UIConst::UI_REQUEST_FILTER_COL)."' />";
		}
		if(!empty($this->hrefUtil->defaultURLParams)){
			foreach($this->hrefUtil->defaultURLParams as $name => $value){
				$html .= "<input type='hidden' name='$name' value='$value' />";
			}
		}


		$html .= "<select name='".UIConst::UI_REQUEST_NUMBER."'>";
		$interval = $this->pageSize;
		$max = ceil($this->totalPages/$interval);
		for($i=$interval;$i<=$max*$interval;$i=$i+$interval){
			$html .= "<option onclick='jQuery(\"#paging-form\").submit();' value='$i' ";
			if(TableUI::checkRequest(UIConst::UI_REQUEST_NUMBER) &&  TableUI::getRequest(UIConst::UI_REQUEST_NUMBER) == $i){
				$html .= "selected";
			}
			$html .= ">$i</option>";
		}
		$html .= "</select>";
		$html .= "</form>";

		$ui = str_replace('{PER_PAGE_FORM}',$html,$ui);
		$html = '';


		$ui = str_replace('{CURRENT_PAGE}',$this->page + 1,$ui);
		$ui = str_replace('{TOTAL_PAGES}',$pages,$ui);
		$ui = str_replace('{TOTOAL_ROWS}',$this->totalPages,$ui);


		$href = $this->hrefUtil->generatePagingHref(true,$this->page,$this) ;
		$ui = str_replace('{prev_href}',$href,$ui);


		$href = $this->hrefUtil->generatePagingHref(false,$this->page,$this) ;
		$ui = str_replace('{next_href}',$href,$ui);

		return $ui;
	}


	private function getColumnName($obj){
		$columnName = array();
		$keys = $obj->getKeys($obj->_fields);
		$prefix = get_class($obj).".";
		if(get_class($this->obj) == get_class($obj)){
			$prefix = "";
		}
		foreach($keys as $key){
			$found = false;
			foreach($this->hideColumn as $cols){
				if(strpos($cols,'.') !== false){
					$class = UIUtil::getClassVar($cols);
					if($class[UIConst::UI_TABLE] == get_class($obj) && $class[UIConst::UI_VAR] == $key ){
						$found = true;
						break;
					}
				}else{
					if($key == $cols){
						$found = true;
						break;
					}
				}
			}
			if(!$found){
				$array = array();
				$array[UIConst::UI_NAME] = $prefix.$key;
				$array[UIConst::UI_COLNAME] = $obj->getColumnName($key);
				$array[0] = $key;
				$array[1] = $obj->getColumnName($key);
				$columnName[]= $array;
			}
		}
		return $columnName;
	}
	public function setColumnNameMapping($array){
		$this->columnNameMap = $array;
	}
	public function setHideColumn($array){
		if(!empty($this->hideColumn))
		$this->hideColumn = $array;
		else{
			$this->hideColumn = array_merge($this->hideColumn,$array);
		}
	}
	public function setRemoveSorting($array){
		$this->removeSorting = $array;
	}
	public function setColumnMapping($array){
		$this->columnMapping = $array;
	}
	public function setFilterColumn($array){
		$this->filterColumn = $array;
	}
	public function setProperties($prop){
		$this->style->setProp($prop);
	}
	public function setActionColumn($array){
		$this->actionColumn = $array;
	}
	public function AddModel($array){
		$this->addModel = $array;
	}
	public function setColumnOrder($array){
		$this->orderColumn = $array;
	}
	public function addCustomColumn($array){
		$this->customColumn = $array;
		if(empty($this->columnMapping)){
			$this->columnMapping = $array;
		}else{
			$this->columnMapping = array_merge($this->columnMapping,$array);
		}
	}
	public function addShowOnlyModel($array){
		$map = array();
		foreach($array as $key){
			if(strpos($key,'.') !== false){
				$arr =  UIUtil::getClassVar($key);
				$class = $arr[UIConst::UI_TABLE];
				$var = $arr[UIConst::UI_VAR];
				if(isset($map[$class])){
					$arr = $map[$class];
					array_push($arr,$var);
					$map[$class] = $arr;
				}else{
					$arr = array();
					array_push($arr,$var);
					$map[$class] = $arr;
				}
			}
		}
		if(!empty($map)){
			foreach($map as $class => $vars){
				$obj_r = new $class;
				$fields = $obj_r->getKeys($obj_r->_fields);
				foreach($fields as $f){
					if(!in_array($f,$vars)){
						if($class == get_class($this->obj)){
							array_push($this->hideColumn,$f);
						}else{
							array_push($this->hideColumn,$class.'.'.$f);
						}
					}
				}
			}
		}
	}

	public function setDefaultURLParams($array){
		if(empty($this->hrefUtil->defaultURLParams))
		$this->hrefUtil->defaultURLParams = $array;
		else{
			$this->hrefUtil->defaultURLParams = array_merge($this->hrefUtil->defaultURLParams,$array);
		}
	}
	public function addRowFilter($func){
		$this->rowFilter = $func;
	}
	public function addCustomFilter($array){
		$this->customWhere = $array;
	}
	public function setEmptyTableHTML($html){
		$this->emptyTableHTML = $html;
	}
	public function setEmptyTableMessage($msg){
		$this->emptyTableMessage = $msg;
	}
	public function checkRequest($var){
		if(isset($_REQUEST[UI::UI_REQUEST_ID]) && $_REQUEST[UI::UI_REQUEST_ID] == $this->id)
		return isset($_REQUEST[$var]);
		else
		return false;
	}
	public function getRequest($var){
		if(isset($_REQUEST[UI::UI_REQUEST_ID]) && $_REQUEST[UI::UI_REQUEST_ID] == $this->id)
		return $_REQUEST[$var];
		else
		return "";
	}
	public function setCheckBoxProp($array){
		$this->checkBoxProp = $array;
	}
	/**
	 * Usage
	 * $ui->addFormButton(array(
	 * 'new button' => 'google.com',
	 * 'test button' => 'func'
	 * ));
	 * function func(){
	 * 	return false;
	 * }
	 */
	public function addFormButton($array){
		$this->addFormButton = $array;
	}
	/**
	 * Usage
	 * $ui->setEnableButton(array(
	 * UIConst::UI_BULK_BUTTON_EDIT => false,
	 * UIConst::UI_BULK_BUTTON_DELETE => true,
	 * ));
	 * function func(){
	 * 	return false;
	 * }
	 */
	public function setEnableFormButton($array){
		$this->formButton = $array;
	}

	/**
	 * Usage
	 * setExportOperations(array(
	 *  "Export XYZ2" => 'xyz',
	 *  "Export XYZ3" => 'XYZManager.xyz',
	 * ));
	 * defination: function xyz($cols, $data);
	 *
	 *
	 */
	public function setExportOperations($map){
		$this->exportOperations = $map;
	}
	/**
	 * Usage
	 */
	public function addSearchField($array){
		$this->searchField = $array;
	}
	/**
	 * addCustomSearchManager("date"=>'SearchManager',"field" => 'ClassName');
	 */
	public function addCustomSearchManager($array){
		$this->addCustomSearchManager = $array;
	}
	public $style;
	public $baseStyle;
	public $obj;
	private $totalPages = 0;
	private $hrefUtil;

	private $columnNameMap = array();
	private $hideColumn = array();
	private $removeSorting = array();
	private $columnMapping = array();
	private $filterColumn = array();
	private $actionColumn = array();
	private $orderColumn = array();
	private $addModel = array();

	/**New Function Added v0.2*/
	private $customColumn = array();
	private $checkBoxProp = array();
	private $customWhere = array();
	private $exportOperations = array();
	private $rowFilter = "";
	private $emptyTableHTML = "";
	private $emptyTableMessage = "Table Is Empty!";
	private $formButton = array();
	private $addFormButton = array();
	private $searchField = array();
	public $addCustomSearchManager = array();

}