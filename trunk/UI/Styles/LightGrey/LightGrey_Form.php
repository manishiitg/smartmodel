<?php

class LightGrey_Form extends LightGreyStyleScripts implements ModelStyles_Form{
	public function loadScripts(){
		$script = "
			<script type='text/javascript'>
				jQuery.noConflict();
				jQuery('document').ready(function(){
					jQuery('input[name=dob]').datepicker({ dateFormat: 'yy-mm-dd' });
				});
			</script>
		";
		return $script;
	}
	/**
	 * $required If this form field is required by user
	 * {name} Name of the label
	 * {inputname} The name attribute of the form field
	 */
	public function getFormLabelHTML($required = true){
		$var = $required ? '' : '';
		$html = "
			<th {prop}>{name}<span class='required'>$var</span>:</th>
		";
		return $html;
	}
	public function getInputHTML($type){
		if($type == FormUI::FORM_FIELD_TEXTFIELD){
			$html = "
				<td><input type='text' name='{name}'  value='{value}' {prop}/></td>
			";	
		}else if($type == FormUI::FORM_FIELD_TEXTAREA){
			$html = "
				<td><textarea name='{name}' cols=30 rows=10 {prop}>{value}</textarea></td>
			";
		}else if($type == FormUI::FORM_FIELD_DROPDOWN){
			$html = "
				<td>
				<select name='{name}' {prop}>
					{value}
				</select>
				</td>
			";
		}else if($type == FormUI::FORM_FIELD_MULTISELECT){
			$html = "
				<td>
				<select name='{name}' {prop} MULTIPLE SIZE=4>
					{value}
				</select>
				</td>
			";
		}
		else if($type == UIConst::FORM_FIELD_CREDIT_CARD){
			$html = "
				<td>
				<input type='text' name='card1' value='{value1}' {prop} size=4/>-
				<input type='text' name='card2' value='{value2}' {prop} size=4/>-
				<input type='text' name='card3' value='{value3}' {prop} size=4/>-
				<input type='text' name='card4' value='{value4}' {prop} size=4/>
				</td>
			";
		}else if($type == FormUI::FORM_FIELD_FILEUPLOAD){
			$html = "
				<td>
				<div>{EDIT}</div>
				<input type='file' name='{name}'/>
				</td>
				";

		}else if($type == FormUI::FORM_FIELD_CHECKBOX){
			$html = "
				<td>
				<input type='checkbox' name='{name}' {value}/>
				</td>
				";

		}else if($type == UIConst::FORM_FIELD_DROPDOWN_TEXT){
			$html = "
				<td>
				<select name='{name}' {prop}>
					{value}
				</select>
				<input type='text' name='{name}_others' {prop} />
				</td>
			";
		}

		else if($type == UIConst::FORM_FIELD_DROPDOWN_OTHERS){
			$html = "
				<td>
				<script type='text/javascript'>
				jQuery('document').ready(function(){
					jQuery('select[name={name}]').change(function(){
						var val = jQuery('select[name={name}]').val();
						if(val == 'Other'){
							jQuery('input[name={name}_others]').show();
						}else{
							jQuery('input[name={name}_others]').hide();
						}
					});
				});
				</script>
				<select name='{name}' {prop}>
					{value}
				</select>
				<input type='text' name='{name}_others' {prop} style='display:none'/>
				</td>
			";
		}else{
			$html = "
				
				<input type='hidden' name='{name}' value='{value}' {prop}/>
				
			";
		}
		return $html;
	}
	public function getFormField(){
		$html = '<tr class="odd">{field}</tr>';
		return $html;
	}
	public function getButtonField(){
		$html = '<div align="right" style="padding-right:85px">{buttons}</div>';
		return $html;
	}
	public function getButton($name){
		if($name == UIConst::UI_FORM_SUBMIT){
			$html = '<input type="submit" value="Submit"/>';
		}else {
			$html = '<input type="reset" value="Reset" class="button-secondary"/>';
			$html = '';
		}

		return $html;
	}
	public function getFormHTMLStrcuture(){
		$html = "
			<div style='width:500px'>{MESSAGES}</div>
			<br/>
			<div id='bus'>
			<table class='form-noindent' cellspacing='3' border='0' width='500px'>
				<tr>
					<td nowrap='nowrap' bgcolor='#e8eefa' valign='top' align='center' style='background: rgb(255, 255, 255) url(images/tr_bck1.gif) repeat scroll 0pt 0pt; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;'>
					
						<form method='{method}' action='{action}' enctype='{enctype}' >
							{HIDDEN_TYPE}
							<table class='form' width='100%' border='0' bgcolor='#ffffff' width='100%'>
								{FORM_FIELDS}
							</table>
							{BUTTONS}
						</form>
					</td>
				</tr>
			</table>
			</div>
		";
		return $html;
	}
}