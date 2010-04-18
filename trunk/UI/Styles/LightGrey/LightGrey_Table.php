<?php

class LightGrey_Table extends LightGreyStyleScripts implements ModelStyles_Table{
	private $ui = array(
	);
	public function setProp($array){
		$this->ui = $array;
	}
	public function loadScripts(){
		$script = "
			<script type='text/javascript'>
				jQuery('document').ready(function(){
					
				});
			</script>
		";
		return $script;
	}
	public function getBulkHTML(){
		$html = "{ADD NEW}";
		$html .= "{EDIT}";
		$html .= "{DELETE}";
		$html .= "{SELECT ALL}";
		$html .= "{SELECT NONE}";
		return $html;
	}
	public function getBulkHTMLButton($type){
		$html = "<a class='ovalbutton' href='{href}' id='{id}'><span>$type</span></a><span style='padding-left:10px'></span>";
		return $html;
	}
	public function getCheckBoxHTML($row){
		return "<input type='checkbox' id='{id}' name='{name}' class='".UIConst::UI_CHECKBOX_CLASS."' />";
	}
	public function getUtilHTML($name){
		$html = '';
		$html .= '<a onclick="" id="" href="{href}" class="ovalbutton"><span>'.$name.'</span></a>';
		return $html;
	}
	public function getFilterHTML(){
		$html = '';
		$html .= '<span style="margin-left:20px">Filter By: ';
		$html .= '{FILTER_FORMS}';
		$html .= '</span>';
		return $html;

	}
	public function getFilterBox(){
		$html = '';
		$html .= '<span>{FILTER_NAME}</span>';
		$html .= '<span>{FILTER_FORM}</span>';
		return $html;
	}
	public function getContentCellHTML($first = false,$last = false,$number = -1,$isAction = false){
		$html = '<td>{CELL}</td>';
		return $html;
	}
	public function getContentRowHTML($number = -1){
		$html = '<tr>{ROW}</tr>';
		return $html;
	}
	public function getContentActionLink(){
		$html = '<a class="menuitem" href="{href}">{name}</a><br/>';
		return $html;
	}


	public function getHeaderRow($name,$first = false,$last = false,$number = -1,$sort = false,$href = '',$type = 1,$showImg = false){
		if(!$sort){
			$html = "<th class='head' scope='col'>$name</th>";
		}else{
			$html = "<th class='head' scope='col'><a style='text-decoration:none' href='{href}'>$name</a>";
			if($showImg){
				$html .= "<img src='{image}'/>";
			}
			$html .= "</th>";
		}
		$html = str_replace('{href}',$href,$html);
		if($showImg){
			if($type == TableUI::SORT_DESC){
				$html = str_replace('{image}','downarrow.png',$html);
			}else{
				$html = str_replace('{image}','uparrow.png',$html);
			}
		}else{
			$html = str_replace('{image}','',$html);
		}
		return $html;
	}
	public function getPagingHTML($hasNext,$hasPrev){
		$html = "";
		$html .= "<span style='margin-left:20px'>View Per Page: {PER_PAGE_FORM} </span>";
		$html .= "<span style='margin-left:20px'>Current Page: {CURRENT_PAGE}</span>";
		$html .= "<span style='margin-left:20px'>Total Page: {TOTAL_PAGES}</span>";
		$html .= "<span style='margin-left:20px'>Total Rows: {TOTOAL_ROWS}</span>";
		if($hasNext)
		$html .= "<a onclick='' id='' href='{next_href}' class='ovalbutton' style='margin-left: 10px;'><span>Next</span></a>";
		if($hasPrev)
		$html .= "<a onclick='' id='' href='{prev_href}' class='ovalbutton' style='margin-left: 10px;'><span>Previous</span></a>";
		return $html;
	}


	public function getTableHTMLStructure(){
		$html = '
		<div>{MESSAGES}</div>
		<br/>
		<div id="bus">
		{ADVANCED_SEARCH}
		<table cellpadding="1" border="0" width="100%">
			<tbody>
				<tr>
					<td>
						<div style="float:right">
							{UTIL_SECTION}
						</div>
						<div style="float:left">
							{BULK_SECTION}
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="float:left">
							{PAGING_FORM}
						</div>
						<div style="float:right">
							{FILTER_FIELDS}
						</div>
					</td>
				</tr>
				<tr>
					<td style="background: rgb(255, 255, 255) url(images/tr_bck.gif) repeat scroll 0pt 0pt; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;">
						<table border="0" bgcolor="#ffffff" width="100%">
							<thead>
								<tr>
									{TABLE_HEADER}
								</tr>
							</thead>
							<tbody>
								<tr>
									{TABLE_CONTENT}
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<div style="float:left">
							{PAGING_FORM}
						</div>
						<div style="float:right">
							{FILTER_FIELDS}
						</div>
					</td>
				</tr>
			</tbody>
			</div>';
		return $html;
	}

	public function getFormStructure(){
		$html = "
			<table class='form-noindent' cellspacing='3' border='0' width=100%>
				<tr>
					<td nowrap='nowrap' bgcolor='#e8eefa' valign='top' align='center' style='background: rgb(255, 255, 255) url(images/tr_bck1.gif) repeat scroll 0pt 0pt; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;'>
						<form method='get'>
							{HIDDEN_TYPE}
						<table class='form' width='100%' border='0' bgcolor='#ffffff' width='100%'>
							{FORM_FIELDS}
						</table>
						<div align=right>
						<input type='submit' value='Search'/>
						<div>
					</form>
					
					</td>
				</tr>
			</table>
		";
		return $html;
	}
	public function getFormLabelHTML($name,$row,$col){
		$html = "
			<th>$name</th>
		";
		return $html;
	}
	public function getInputHTML($type){
		if($type == FormUI::FORM_FIELD_TEXTFIELD){
			$html = "
				<td><input type='text' name='{name}' value='{value}'/></td>
			";	
		}else if($type == FormUI::FORM_FIELD_TEXTAREA){
			$html = "
				<td><textarea name='{name}'>{value}</textarea></td>
			";
		}else if($type == FormUI::FORM_FIELD_DROPDOWN){
			$html = "
				<td>
				<select name='{name}'>
					{value}
				</select>
				</td>
			";
		}else{
			$html = "
				<input type='hidden' name='{name}' value='{value}' />
			";
		}
		return $html;
	}
	public function getFormFieldWrapper($row,$col){
		$html = '<tr class="odd">{field}</tr>';
		return $html;
	}
}