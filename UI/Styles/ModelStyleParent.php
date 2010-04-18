<?php

class ModelStyleParent{
	public function selectAllScript(){
		$id = str_replace(' ','_',UIConst::UI_BULK_BUTTON_SELECT_ALL);
		$script = "
		<script type='text/javascript'>
			jQuery.noConflict();	
			jQuery(document).ready(function(){
				jQuery('#$id').click(function(){
					jQuery('td input.ui_checkbox').attr('checked','checked');
				});
			});
		</script>
		";
		return $script;
	}
	public function selectNoneScript(){
		$id = str_replace(' ','_',UIConst::UI_BULK_BUTTON_SELECT_NONE);
		$script = "
		<script type='text/javascript'>
			jQuery.noConflict();	
			jQuery(document).ready(function(){
				jQuery('#$id').click(function(){
					jQuery('td input.ui_checkbox').attr('checked','');
				});
			});
		</script>
		";
		return $script;
	}
	public function selectCloneScript($href){
		$id = str_replace(' ','_',UIConst::UI_BULK_BUTTON_CLONE);
		$check = $this->checkSelectedCheckBox();
		$script = "
		<script type='text/javascript'>
			jQuery.noConflict();	
			jQuery(document).ready(function(){
				jQuery('#$id').click(function(){
				$check
				});
			});
		</script>
		";
				return $script;
	}
	public function selectAddNewScript($href){

	}
	public function selectEditScript($href){
		$id = str_replace(' ','_',UIConst::UI_BULK_BUTTON_EDIT);
		$check = $this->checkSelectedCheckBox();
		$URL = $_SERVER['PHP_SELF'] .'?'. $href .'&'. UIConst::UI_FORM_ITEM_ID . '=';
		$script = "
		<script type='text/javascript'>
			jQuery.noConflict();	
			jQuery(document).ready(function(){
				jQuery('#$id').click(function(){
				$check
					var name = jQuery('input.ui_checkbox:checked').attr('name')
					window.location.href = '$URL'+name;
				});
			});
		</script>
		";
				return $script;
	}
	public function selectDeleteScript($href){
		$id = str_replace(' ','_',UIConst::UI_BULK_BUTTON_DELETE);
		$check = $this->checkSelectedCheckBox();
		$URL = $_SERVER['PHP_SELF'] .'?'. $href .'&'. UIConst::UI_FORM_ITEM_ID . '=';
		$script = "
		<script type='text/javascript'>
			jQuery.noConflict();	
			jQuery(document).ready(function(){
				jQuery('#$id').click(function(){
				$check
				var ids = '';
				jQuery('input.ui_checkbox:checked').each(function(ele){
				    ids += jQuery(this).attr('name')+',';
				});
				ids = ids.substr(0,ids.length-1);
				window.location.href = '$URL'+ids;
				});
			});
		</script>
		";
				return $script;
	}
	public function checkSelectedCheckBox($msg = ''){
		if(empty($msg)){
			$msg = 'Please Select Required Row';
		}
		$script = "
				if(jQuery('input.ui_checkbox:checked').size() <= 0){
					alert('$msg');
					return false;	
				}
		";
		return $script;
	}
}