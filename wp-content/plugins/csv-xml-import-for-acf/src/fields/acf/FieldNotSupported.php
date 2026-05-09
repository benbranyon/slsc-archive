<?php

namespace pmai_acf_add_on\fields\acf;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use pmai_acf_add_on\fields\Field;

/**
 * Class FieldEmpty
 * @package pmai_acf_add_on\fields\acf
 */
class FieldNotSupported extends Field {

    /**
     *  Field type key
     */
    public $type = 'not_supported';

}