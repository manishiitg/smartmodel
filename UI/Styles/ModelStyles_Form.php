<?php

interface ModelStyles_Form{
	public function getInputHTML($type);
	public function getFormLabelHTML($required = true);
	public function getFormField();
	public function getFormHTMLStrcuture();
	public function getButtonField();
	public function getButton($name);
	public function loadScripts();
}