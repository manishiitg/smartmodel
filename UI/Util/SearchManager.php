<?php

interface SearchManager {
	public function processSearch($key,$name_prefix);
	public function getInputTypeHTML($name_prefix);
}