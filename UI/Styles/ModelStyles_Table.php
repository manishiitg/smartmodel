<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */
interface ModelStyles_Table{
	/* Not Used Any More*/
	public function getUtilHTML($name);
	public function getFilterHTML();
	public function getFilterBox();
	public function getContentCellHTML($first = false,$last = false,$number = -1,$isAction = false);
	public function getContentRowHTML($number = -1);
	public function getContentActionLink();
	public function getHeaderRow($name,$first = false,$last = false,$number = -1,$sort = false,$href = '',$type = 1,$showImg = false);
	public function getPagingHTML($hasNext,$hasPrev);
	public function getTableHTMLStructure();

	/**
	 * Bulk Operations like checkbox, add new, edit ,delete etc
	 * @return html code string
	 */
	public function getBulkHTML();
	public function getCheckBoxHTML($row);
	public function loadScripts();
	public function getFormStructure();
	public function getFormLabelHTML($name,$row,$col);
	public function getFormFieldWrapper($row,$col);
	public function getInputHTML($type);
}