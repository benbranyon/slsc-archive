<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PMAI_Import_List extends PMAI_Model_List {
	public function __construct() {
		parent::__construct();
		$this->setTable(PMAI_Plugin::getInstance()->getTablePrefix() . 'imports');
	}
}