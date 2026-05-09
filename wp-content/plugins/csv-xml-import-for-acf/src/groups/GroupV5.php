<?php

namespace pmai_acf_add_on\groups;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class GroupV5
 * @package pmai_acf_add_on\groups
 */
class GroupV5 extends Group {

    /**
     *  Init field for ACF v5.x
     */
    public function initFields() {

	    $acf_fields = acf_get_fields($this->group['ID']);
        if (!empty($acf_fields)) {
            foreach ($acf_fields as $field) {
                $this->fieldsData[] = $field;
            }
        }
        // create field instances
        parent::initFields();
    }
}