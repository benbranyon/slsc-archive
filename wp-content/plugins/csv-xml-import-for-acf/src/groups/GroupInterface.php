<?php

namespace pmai_acf_add_on\groups;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Interface FieldInterface
 * @package pmai_acf_add_on\groups
 */
interface GroupInterface{

    /**
     * @return mixed
     */
    public function initFields();

    /**
     * @return mixed
     */
    public function view();

    /**
     * @param $parsingData
     * @return mixed
     */
    public function parse($parsingData);

    /**
     * @param $importData
     * @return mixed
     */
    public function saved_post($importData);

}