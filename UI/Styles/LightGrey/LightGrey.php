<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */

require dirname(__FILE__).'/LightGreyStyleScripts.php';
class LightGrey extends LightGreyStyleScripts implements ModelStyles {
	public function getErrorMessage($msg){
		$html = "
			<div align='left'>
			<ul class='messages'>
				<li class='error-msg'>
				<ul>
					<li>$msg</li>
				</ul>
				</li>
			</ul>
			</div>
		";
		return $html;
	}
	public function getSucessMessage($msg){
		$html = "
		<div align='left'>
			<ul class='messages'>
				<li class='success-msg'>
				<ul>
					<li>$msg</li>
				</ul>
				</li>
			</ul>
			</div>
		";
		return $html;
	}
}