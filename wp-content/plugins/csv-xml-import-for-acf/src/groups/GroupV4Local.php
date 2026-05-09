<?php

namespace pmai_acf_add_on\groups;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class GroupV4Local
 * @package pmai_acf_add_on\groups
 */
class GroupV4Local extends Group {

    /**
     *  Init group fields which are saved locally in the code for ACF v4.x
     */
    public function initFields() {

		// $acf_register_field_group is provided by the Advanced Custom Fields plugin.
        global $acf_register_field_group;

        if (!empty($acf_register_field_group)) {
            foreach ($acf_register_field_group as $key => $group) {
                if ($group['id'] == $this->group['ID']) {
                    $this->fieldsData = $group['fields'];
                    break;
                }
            }
        }
        // create field instances
        parent::initFields();
    }
}